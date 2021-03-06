<?php

/**
 * This file is part of the eZ Platform Solr Search Engine package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\EzPlatformSolrSearchEngine\DocumentMapper;

use EzSystems\EzPlatformSolrSearchEngine\DocumentMapper;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Type as ContentType;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Section;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\Document;
use eZ\Publish\SPI\Search\FieldType;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as ObjectStateHandler;
use eZ\Publish\SPI\Persistence\Content\Section\Handler as SectionHandler;
use eZ\Publish\Core\Search\Common\FieldRegistry;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;

/**
 * NativeDocumentMapper maps Solr backend documents per Content translation.
 */
class NativeDocumentMapper implements DocumentMapper
{
    /**
     * Field registry.
     *
     * @var \eZ\Publish\Core\Search\Common\FieldRegistry
     */
    protected $fieldRegistry;

    /**
     * Content handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    protected $contentHandler;

    /**
     * Location handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    protected $locationHandler;

    /**
     * Content type handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * Object state handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
     */
    protected $objectStateHandler;

    /**
     * Section handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    protected $sectionHandler;

    /**
     * Field name generator.
     *
     * @var \eZ\Publish\Core\Search\Common\FieldNameGenerator
     */
    protected $fieldNameGenerator;

    /**
     * Creates a new document mapper.
     *
     * @param \eZ\Publish\Core\Search\Common\FieldRegistry $fieldRegistry
     * @param \eZ\Publish\SPI\Persistence\Content\Handler $contentHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Handler $locationHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler $objectStateHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Section\Handler $sectionHandler
     * @param \eZ\Publish\Core\Search\Common\FieldNameGenerator $fieldNameGenerator
     */
    public function __construct(
        FieldRegistry $fieldRegistry,
        ContentHandler $contentHandler,
        LocationHandler $locationHandler,
        ContentTypeHandler $contentTypeHandler,
        ObjectStateHandler $objectStateHandler,
        SectionHandler $sectionHandler,
        FieldNameGenerator $fieldNameGenerator
    ) {
        $this->fieldRegistry = $fieldRegistry;
        $this->contentHandler = $contentHandler;
        $this->locationHandler = $locationHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->objectStateHandler = $objectStateHandler;
        $this->sectionHandler = $sectionHandler;
        $this->fieldNameGenerator = $fieldNameGenerator;
    }

    /**
     * Maps given Content to a Document.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return \eZ\Publish\SPI\Search\Document[]
     */
    public function mapContentBlock(Content $content)
    {
        $locations = $this->locationHandler->loadLocationsByContent($content->versionInfo->contentInfo->id);
        $section = $this->sectionHandler->load($content->versionInfo->contentInfo->sectionId);
        $mainLocation = null;
        $isSomeLocationVisible = false;
        $locationData = array();
        $locationFields = array();

        foreach ($locations as $location) {
            $locationFields[$location->id] = $this->mapLocationFields($location, $content, $section);

            $locationData['ids'][] = $location->id;
            $locationData['parent_ids'][] = $location->parentId;
            $locationData['remote_ids'][] = $location->remoteId;
            $locationData['path_strings'][] = $location->pathString;

            if ($location->id == $content->versionInfo->contentInfo->mainLocationId) {
                $mainLocation = $location;
            }

            if (!$location->hidden && !$location->invisible) {
                $isSomeLocationVisible = true;
            }
        }

        // UserGroups and Users are Content, but permissions cascade is achieved through
        // Locations hierarchy. We index all ancestor Location Content ids of all
        // Locations of an owner.
        $ancestorLocationsContentIds = $this->getAncestorLocationsContentIds(
            $content->versionInfo->contentInfo->ownerId
        );
        // Add owner user id as it can also be considered as user group.
        $ancestorLocationsContentIds[] = $content->versionInfo->contentInfo->ownerId;

        $fields = array(
            new Field(
                'content',
                $content->versionInfo->contentInfo->id,
                new FieldType\IdentifierField()
            ),
            new Field(
                'document_type',
                self::DOCUMENT_TYPE_IDENTIFIER_CONTENT,
                new FieldType\IdentifierField()
            ),
            new Field(
                'type',
                $content->versionInfo->contentInfo->contentTypeId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'version_no',
                $content->versionInfo->versionNo,
                new FieldType\IntegerField()
            ),
            new Field(
                'status',
                $content->versionInfo->status,
                new FieldType\IdentifierField()
            ),
            new Field(
                'name',
                $content->versionInfo->contentInfo->name,
                new FieldType\StringField()
            ),
            new Field(
                'creator',
                $content->versionInfo->creatorId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'owner',
                $content->versionInfo->contentInfo->ownerId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'owner_user_group',
                $ancestorLocationsContentIds,
                new FieldType\MultipleIdentifierField()
            ),
            new Field(
                'section',
                $content->versionInfo->contentInfo->sectionId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'section_identifier',
                $section->identifier,
                new FieldType\IdentifierField()
            ),
            new Field(
                'section_name',
                $section->name,
                new FieldType\StringField()
            ),
            new Field(
                'remote_id',
                $content->versionInfo->contentInfo->remoteId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'modified',
                $content->versionInfo->contentInfo->modificationDate,
                new FieldType\DateField()
            ),
            new Field(
                'published',
                $content->versionInfo->contentInfo->publicationDate,
                new FieldType\DateField()
            ),
            new Field(
                'language_code',
                array_keys($content->versionInfo->names),
                new FieldType\MultipleStringField()
            ),
            new Field(
                'main_language_code',
                $content->versionInfo->contentInfo->mainLanguageCode,
                new FieldType\StringField()
            ),
            new Field(
                'always_available',
                $content->versionInfo->contentInfo->alwaysAvailable,
                new FieldType\BooleanField()
            ),
            new Field(
                'location_visible',
                $isSomeLocationVisible,
                new FieldType\BooleanField()
            ),
        );

        if (!empty($locationData)) {
            $fields[] = new Field(
                'location_id',
                $locationData['ids'],
                new FieldType\MultipleIdentifierField()
            );
            $fields[] = new Field(
                'location_parent_id',
                $locationData['parent_ids'],
                new FieldType\MultipleIdentifierField()
            );
            $fields[] = new Field(
                'location_remote_id',
                $locationData['remote_ids'],
                new FieldType\MultipleIdentifierField()
            );
            $fields[] = new Field(
                'location_path_string',
                $locationData['path_strings'],
                new FieldType\MultipleIdentifierField()
            );
        }

        if ($mainLocation !== null) {
            $fields[] = new Field(
                'main_location',
                $mainLocation->id,
                new FieldType\IdentifierField()
            );
            $fields[] = new Field(
                'main_location_parent',
                $mainLocation->parentId,
                new FieldType\IdentifierField()
            );
            $fields[] = new Field(
                'main_location_remote_id',
                $mainLocation->remoteId,
                new FieldType\IdentifierField()
            );
            $fields[] = new Field(
                'main_location_visible',
                !$mainLocation->hidden && !$mainLocation->invisible,
                new FieldType\BooleanField()
            );
            $fields[] = new Field(
                'main_location_path',
                $mainLocation->pathString,
                new FieldType\IdentifierField()
            );
            $fields[] = new Field(
                'main_location_depth',
                $mainLocation->depth,
                new FieldType\IntegerField()
            );
            $fields[] = new Field(
                'main_location_priority',
                $mainLocation->priority,
                new FieldType\IntegerField()
            );
        }

        $contentType = $this->contentTypeHandler->load($content->versionInfo->contentInfo->contentTypeId);
        $fields[] = new Field(
            'group',
            $contentType->groupIds,
            new FieldType\MultipleIdentifierField()
        );

        $fields[] = new Field(
            'object_state',
            $this->getObjectStateIds($content->versionInfo->contentInfo->id),
            new FieldType\MultipleIdentifierField()
        );

        $fieldSets = $this->mapContentFields($content, $contentType);
        $documents = array();

        foreach ($fieldSets as $languageCode => $translationFields) {
            $metaFields = array();
            $metaFields[] = new Field(
                'meta_indexed_language_code',
                $languageCode,
                new FieldType\StringField()
            );
            $metaFields[] = new Field(
                'meta_indexed_is_main_translation',
                ($languageCode === $content->versionInfo->contentInfo->mainLanguageCode),
                new FieldType\BooleanField()
            );
            $metaFields[] = new Field(
                'meta_indexed_is_main_translation_and_always_available',
                (
                    ($languageCode === $content->versionInfo->contentInfo->mainLanguageCode) &&
                    $content->versionInfo->contentInfo->alwaysAvailable
                ),
                new FieldType\BooleanField()
            );

            $translationLocationDocuments = array();
            foreach ($locations as $location) {
                $translationLocationDocuments[] = new Document(
                    array(
                        'id' => $this->generateLocationDocumentId($location->id, $languageCode),
                        'fields' => array_merge(
                            $locationFields[$location->id],
                            $translationFields['regular'],
                            $metaFields
                        ),
                    )
                );
            }

            $isMainTranslation = ($content->versionInfo->contentInfo->mainLanguageCode === $languageCode);
            $alwaysAvailable = ($isMainTranslation && $content->versionInfo->contentInfo->alwaysAvailable);

            $documents[] = new Document(
                array(
                    'id' => $this->generateContentDocumentId(
                        $content->versionInfo->contentInfo->id,
                        $languageCode
                    ),
                    'languageCode' => $languageCode,
                    'alwaysAvailable' => $alwaysAvailable,
                    'isMainTranslation' => $isMainTranslation,
                    'fields' => array_merge(
                        $fields,
                        $translationFields['regular'],
                        $translationFields['fulltext'],
                        $metaFields
                    ),
                    'documents' => $translationLocationDocuments,
                )
            );
        }

        return $documents;
    }

    /**
     * Generates the Solr backend document ID for Content object.
     *
     * If $language code is not provided, the method will return prefix of the IDs
     * of all Content's documents (there will be one document per translation).
     * The above is useful when targeting all Content's documents, without
     * the knowledge of it's translations.
     *
     * @param int|string $contentId
     * @param null|string $languageCode
     *
     * @return string
     */
    public function generateContentDocumentId($contentId, $languageCode = null)
    {
        return strtolower("content{$contentId}{$languageCode}");
    }

    /**
     * Generates the Solr backend document ID for Location object.
     *
     * If $language code is not provided, the method will return prefix of the IDs
     * of all Location's documents (there will be one document per translation).
     * The above is useful when targeting all Location's documents, without
     * the knowledge of it's Content's translations.
     *
     * @param int|string $locationId
     * @param null|string $languageCode
     *
     * @return string
     */
    public function generateLocationDocumentId($locationId, $languageCode = null)
    {
        return strtolower("location{$locationId}{$languageCode}");
    }

    /**
     * Maps given Content fields to a map Document fields.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param \eZ\Publish\SPI\Persistence\Content\Type $contentType
     *
     * @return \eZ\Publish\SPI\Search\Field[][][]
     */
    protected function mapContentFields(Content $content, ContentType $contentType)
    {
        $fieldSets = array();

        foreach ($content->fields as $field) {
            if (!isset($fieldSets[$field->languageCode])) {
                $fieldSets[$field->languageCode] = array(
                    'regular' => array(),
                    'fulltext' => array(),
                );
            }

            foreach ($contentType->fieldDefinitions as $fieldDefinition) {
                if ($fieldDefinition->id !== $field->fieldDefinitionId) {
                    continue;
                }

                $fieldType = $this->fieldRegistry->getType($field->type);
                $indexFields = $fieldType->getIndexData($field, $fieldDefinition);

                foreach ($indexFields as $indexField) {
                    if ($indexField->value === null) {
                        continue;
                    }

                    $documentField = new Field(
                        $name = $this->fieldNameGenerator->getName(
                            $indexField->name,
                            $fieldDefinition->identifier,
                            $contentType->identifier
                        ),
                        $indexField->value,
                        $indexField->type
                    );

                    if ($documentField->type instanceof FieldType\FullTextField) {
                        $fieldSets[$field->languageCode]['fulltext'][] = $documentField;
                    } else {
                        $fieldSets[$field->languageCode]['regular'][] = $documentField;
                    }
                }
            }
        }

        return $fieldSets;
    }

    protected function mapLocationFields(Location $location, Content $content, Section $section)
    {
        $fields = array(
            new Field(
                'location',
                $location->id,
                new FieldType\IdentifierField()
            ),
            new Field(
                'document_type',
                self::DOCUMENT_TYPE_IDENTIFIER_LOCATION,
                new FieldType\IdentifierField()
            ),
            new Field(
                'priority',
                $location->priority,
                new FieldType\IntegerField()
            ),
            new Field(
                'hidden',
                $location->hidden,
                new FieldType\BooleanField()
            ),
            new Field(
                'invisible',
                $location->invisible,
                new FieldType\BooleanField()
            ),
            new Field(
                'remote_id',
                $location->remoteId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'parent_id',
                $location->parentId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'path_string',
                $location->pathString,
                new FieldType\IdentifierField()
            ),
            new Field(
                'depth',
                $location->depth,
                new FieldType\IntegerField()
            ),
            new Field(
                'sort_field',
                $location->sortField,
                new FieldType\IdentifierField()
            ),
            new Field(
                'sort_order',
                $location->sortOrder,
                new FieldType\IdentifierField()
            ),
            new Field(
                'is_main_location',
                ($location->id == $content->versionInfo->contentInfo->mainLocationId),
                new FieldType\BooleanField()
            ),
        );

        // UserGroups and Users are Content, but permissions cascade is achieved through
        // Locations hierarchy. We index all ancestor Location Content ids of all
        // Locations of an owner.
        $ancestorLocationsContentIds = $this->getAncestorLocationsContentIds(
            $content->versionInfo->contentInfo->ownerId
        );
        // Add owner user id as it can also be considered as user group.
        $ancestorLocationsContentIds[] = $content->versionInfo->contentInfo->ownerId;
        $fields[] = new Field(
            'content_owner_user_group',
            $ancestorLocationsContentIds,
            new FieldType\MultipleIdentifierField()
        );

        $fields[] = new Field(
            'content_id',
            $content->versionInfo->contentInfo->id,
            new FieldType\IdentifierField()
        );
        $fields[] = new Field(
            'content_type',
            $content->versionInfo->contentInfo->contentTypeId,
            new FieldType\IdentifierField()
        );
        $fields[] = new Field(
            'content_version_no',
            $content->versionInfo->versionNo,
            new FieldType\IntegerField()
        );
        $fields[] = new Field(
            'content_status',
            $content->versionInfo->status,
            new FieldType\IdentifierField()
        );
        $fields[] = new Field(
            'content_name',
            $content->versionInfo->contentInfo->name,
            new FieldType\StringField()
        );
        $fields[] = new Field(
            'content_creator',
            $content->versionInfo->creatorId,
            new FieldType\IdentifierField()
        );
        $fields[] = new Field(
            'content_owner',
            $content->versionInfo->contentInfo->ownerId,
            new FieldType\IdentifierField()
        );
        $fields[] = new Field(
            'content_section',
            $content->versionInfo->contentInfo->sectionId,
            new FieldType\IdentifierField()
        );
        $fields[] = new Field(
            'content_section_identifier',
            $section->identifier,
            new FieldType\IdentifierField()
        );
        $fields[] = new Field(
            'content_section_name',
            $section->name,
            new FieldType\StringField()
        );
        $fields[] = new Field(
            'content_remote_id',
            $content->versionInfo->contentInfo->remoteId,
            new FieldType\IdentifierField()
        );
        $fields[] = new Field(
            'content_modified',
            $content->versionInfo->contentInfo->modificationDate,
            new FieldType\DateField()
        );
        $fields[] = new Field(
            'content_published',
            $content->versionInfo->contentInfo->publicationDate,
            new FieldType\DateField()
        );
        $fields[] = new Field(
            'language_code',
            array_keys($content->versionInfo->names),
            new FieldType\MultipleStringField()
        );
        $fields[] = new Field(
            'content_always_available',
            $content->versionInfo->contentInfo->alwaysAvailable,
            new FieldType\BooleanField()
        );
        $fields[] = new Field(
            'content_group',
            $this->contentTypeHandler->load(
                $content->versionInfo->contentInfo->contentTypeId
            )->groupIds,
            new FieldType\MultipleIdentifierField()
        );
        $fields[] = new Field(
            'content_object_state',
            $this->getObjectStateIds($content->versionInfo->contentInfo->id),
            new FieldType\MultipleIdentifierField()
        );

        return $fields;
    }

    /**
     * Returns Content ids of all ancestor Locations of all Locations
     * of a Content with given $contentId.
     *
     * Used to determine user groups of a user with $contentId.
     *
     * @param int|string $contentId
     *
     * @return array
     */
    protected function getAncestorLocationsContentIds($contentId)
    {
        $locations = $this->locationHandler->loadLocationsByContent($contentId);
        $ancestorLocationContentIds = array();
        $ancestorLocationIds = array();

        foreach ($locations as $location) {
            $locationIds = explode('/', trim($location->pathString, '/'));
            // Remove Location of Content with $contentId
            array_pop($locationIds);
            // Remove Root Location id (id==1 in legacy DB)
            array_shift($locationIds);

            $ancestorLocationIds = array_merge($ancestorLocationIds, $locationIds);
        }

        foreach (array_unique($ancestorLocationIds) as $locationId) {
            $location = $this->locationHandler->load($locationId);

            $ancestorLocationContentIds[$location->contentId] = true;
        }

        return array_keys($ancestorLocationContentIds);
    }

    /**
     * Returns an array of object state ids of a Content with given $contentId.
     *
     * @param int|string $contentId
     *
     * @return array
     */
    protected function getObjectStateIds($contentId)
    {
        $objectStateIds = array();

        foreach ($this->objectStateHandler->loadAllGroups() as $objectStateGroup) {
            $objectStateIds[] = $this->objectStateHandler->getContentState(
                $contentId,
                $objectStateGroup->id
            )->id;
        }

        return $objectStateIds;
    }
}

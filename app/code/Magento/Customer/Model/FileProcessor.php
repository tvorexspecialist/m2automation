<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

/**
 * Class \Magento\Customer\Model\FileProcessor
 *
 * @since 2.1.3
 */
class FileProcessor
{
    /**
     * Temporary directory name
     */
    const TMP_DIR = 'tmp';

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     * @since 2.1.3
     */
    private $mediaDirectory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     * @since 2.1.3
     */
    private $uploaderFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     * @since 2.1.3
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     * @since 2.1.3
     */
    private $urlEncoder;

    /**
     * @var string
     * @since 2.1.3
     */
    private $entityTypeCode;

    /**
     * @var array
     * @since 2.1.3
     */
    private $allowedExtensions = [];

    /**
     * @var \Magento\Framework\File\Mime
     * @since 2.1.3
     */
    private $mime;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param string $entityTypeCode
     * @param \Magento\Framework\File\Mime $mime
     * @param array $allowedExtensions
     * @since 2.1.3
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        $entityTypeCode,
        \Magento\Framework\File\Mime $mime,
        array $allowedExtensions = []
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->uploaderFactory = $uploaderFactory;
        $this->urlBuilder = $urlBuilder;
        $this->urlEncoder = $urlEncoder;
        $this->entityTypeCode = $entityTypeCode;
        $this->mime = $mime;
        $this->allowedExtensions = $allowedExtensions;
    }

    /**
     * Retrieve base64 encoded file content
     *
     * @param string $fileName
     * @return string
     * @since 2.1.3
     */
    public function getBase64EncodedData($fileName)
    {
        $filePath = $this->entityTypeCode . '/' . ltrim($fileName, '/');

        $fileContent = $this->mediaDirectory->readFile($filePath);

        $encodedContent = base64_encode($fileContent);
        return $encodedContent;
    }

    /**
     * Get file statistics data
     *
     * @param string $fileName
     * @return array
     * @since 2.1.3
     */
    public function getStat($fileName)
    {
        $filePath = $this->entityTypeCode . '/' . ltrim($fileName, '/');

        $result = $this->mediaDirectory->stat($filePath);
        return $result;
    }

    /**
     * Retrieve MIME type of requested file
     *
     * @param string $fileName
     * @return string
     * @since 2.1.3
     */
    public function getMimeType($fileName)
    {
        $filePath = $this->entityTypeCode . '/' . ltrim($fileName, '/');
        $absoluteFilePath = $this->mediaDirectory->getAbsolutePath($filePath);

        $result = $this->mime->getMimeType($absoluteFilePath);
        return $result;
    }

    /**
     * Check if the file exists
     *
     * @param string $fileName
     * @return bool
     * @since 2.1.3
     */
    public function isExist($fileName)
    {
        $filePath = $this->entityTypeCode . '/' . ltrim($fileName, '/');

        $result = $this->mediaDirectory->isExist($filePath);
        return $result;
    }

    /**
     * Retrieve customer/index/viewfile action URL
     *
     * @param string $filePath
     * @param string $type
     * @return string
     * @since 2.1.3
     */
    public function getViewUrl($filePath, $type)
    {
        $viewUrl = '';

        if ($this->entityTypeCode == \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS) {
            $filePath = $this->entityTypeCode . '/' . ltrim($filePath, '/');
            $viewUrl = $this->urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA])
                . $this->mediaDirectory->getRelativePath($filePath);
        }

        if ($this->entityTypeCode == \Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER) {
            $viewUrl = $this->urlBuilder->getUrl(
                'customer/index/viewfile',
                [$type => $this->urlEncoder->encode(ltrim($filePath, '/'))]
            );
        }

        return $viewUrl;
    }

    /**
     * Save uploaded file to temporary directory
     *
     * @param string $fileId
     * @return \string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.1.3
     */
    public function saveTemporaryFile($fileId)
    {
        /** @var \Magento\MediaStorage\Model\File\Uploader $uploader */
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setFilesDispersion(false);
        $uploader->setFilenamesCaseSensitivity(false);
        $uploader->setAllowRenameFiles(true);
        $uploader->setAllowedExtensions($this->allowedExtensions);

        $path = $this->mediaDirectory->getAbsolutePath(
            $this->entityTypeCode . '/' . self::TMP_DIR
        );

        $result = $uploader->save($path);
        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File can not be saved to the destination folder.')
            );
        }

        return $result;
    }

    /**
     * Move file from temporary directory into base directory
     *
     * @param string $fileName
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.1.3
     */
    public function moveTemporaryFile($fileName)
    {
        $fileName = ltrim($fileName, '/');

        $dispersionPath = \Magento\MediaStorage\Model\File\Uploader::getDispretionPath($fileName);
        $destinationPath = $this->entityTypeCode . $dispersionPath;

        if (!$this->mediaDirectory->create($destinationPath)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to create directory %1.', $destinationPath)
            );
        }

        if (!$this->mediaDirectory->isWritable($destinationPath)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Destination folder is not writable or does not exists.')
            );
        }

        $destinationFileName = \Magento\MediaStorage\Model\File\Uploader::getNewFileName(
            $this->mediaDirectory->getAbsolutePath($destinationPath) . '/' . $fileName
        );

        try {
            $this->mediaDirectory->renameFile(
                $this->entityTypeCode . '/' . self::TMP_DIR . '/' . $fileName,
                $destinationPath . '/' . $destinationFileName
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while saving the file.')
            );
        }

        $fileName = $dispersionPath . '/' . $fileName;
        return $fileName;
    }

    /**
     * Remove uploaded file
     *
     * @param string $fileName
     * @return bool
     * @since 2.1.3
     */
    public function removeUploadedFile($fileName)
    {
        $filePath = $this->entityTypeCode . '/' . ltrim($fileName, '/');

        $result = $this->mediaDirectory->delete($filePath);
        return $result;
    }
}

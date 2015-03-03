<?php

namespace GoogleHelper\Drive;

class FilesHelper extends DriveHelper
{

    /**
     * Get a file
     * @param $folderName
     * @param $mimeType
     * @param bool $includeTrash
     * @param \Google_Service_Drive_DriveFile $parent
     * @param bool $createIfNotFound
     * @return \Google_Service_Drive_DriveFile|null
     */
    public function getFileByName(
       $folderName,
       $mimeType,
       $includeTrash = false,
       \Google_Service_Drive_DriveFile $parent = null,
       $createIfNotFound = false
    ) {
        $query = 'title = \'' . $folderName . '\' and mimeType = \'' . $mimeType . '\'';
        if (!$includeTrash) {
            $query .= ' and trashed = false';
        }
        if ($parent !== null) {
            $query .= ' and \'' . $parent->getId() . '\' in parents';
        }
        $fileList = $this->service->files->listFiles(array('q' => $query));
        $folders   = $fileList->getItems();
        if (isset($folders[0])) {
            return $folders[0];
        }
        if ($createIfNotFound) {
            $this->helper->getLogger()->debug('Creating Folder ' . $folderName);
            return $this->createFolder($folderName, $parent);
        }

        return null;
    }

    /**
     * Get folder
     * @param $folderName
     * @param bool $includeTrash
     * @param \Google_Service_Drive_DriveFile $parent
     * @param bool $createIfNotFound
     * @return \Google_Service_Drive_DriveFile|null
     */
    public function getFolderByName(
       $folderName,
       $includeTrash = false,
       \Google_Service_Drive_DriveFile $parent = null,
       $createIfNotFound = false
    ) {
        return $this->getFileByName($folderName, self::MIME_FOLDER, $includeTrash, $parent,
           $createIfNotFound);
    }

    /**
     * Create a file
     * @param $fileName
     * @param $mimeType
     * @param \Google_Service_Drive_DriveFile $parentFolder
     * @return \Google_Service_Drive_DriveFile
     */
    public function createFile(
       $fileName,
       $mimeType,
       \Google_Service_Drive_DriveFile $parentFolder = null
    ) {
        $folder = new \Google_Service_Drive_DriveFile();
        $folder->setTitle($fileName);
        $folder->setMimeType($mimeType);
        if ($parentFolder !== null) {
            $parent = new \Google_Service_Drive_ParentReference();
            $parent->setId($parentFolder->getId());
            $folder->setParents(array($parent));
        }

        return $this->service->files->insert($folder);
    }

    /**
     * Create a folder
     * @param $folderName
     * @param \Google_Service_Drive_DriveFile $parentFolder
     * @return \Google_Service_Drive_DriveFile
     */
    public function createFolder($folderName, \Google_Service_Drive_DriveFile $parentFolder = null)
    {
        return $this->createFile($folderName, self::MIME_FOLDER, $parentFolder);
    }

    /**
     * Upload a file
     * @param string $fileName
     * @param string $fileLocation
     * @param \Google_Service_Drive_DriveFile $folder
     * @param string $uploadType
     * @return \Google_Service_Drive_DriveFile
     */
    public function uploadFile($fileName, $fileLocation, \Google_Service_Drive_DriveFile $folder = null, $uploadType = 'media')
    {
        //Insert a file
        $file = new \Google_Service_Drive_DriveFile();
        $file->setTitle($fileName);
        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($fileLocation);
        $file->setDescription($fileName);
        $file->setMimeType($mimeType);
        if ($folder !== null) {
            $parent = new \Google_Service_Drive_ParentReference();
            $parent->setId($folder->getId());
            $file->setParents(array($parent));
        }
        $data = file_get_contents($fileLocation);
        try {
            return $this->service->files->insert($file, array(
               'data'       => $data,
               'mimeType'   => $file->getMimeType(),
               'uploadType' => $uploadType,
            ));
        } catch (\Google_Service_Exception $obj_ex) {
            $this->service->getClient()->getLogger()->error($obj_ex->getMessage());
            return false;
        }
    }


    /**
     * Download a file from a url
     * @param $url
     * @return string|null
     */
    public function downloadFileFromURL($url)
    {
        $request     = new \Google_Http_Request($url, 'GET', null, null);
        $httpRequest = $this->service->getClient()
           ->getAuth()
           ->authenticatedRequest($request);
        if ($httpRequest->getResponseHttpCode() == 200) {
            return $httpRequest->getResponseBody();
        } else {
            // An error occurred.
            return null;
        }
    }

    /**
     * Get Files in a folder
     * @param \Google_Service_Drive_DriveFile $folder
     * @return \Google_Service_Drive_FileList
     */
    public function getFilesInFolder(\Google_Service_Drive_DriveFile $folder)
    {
        $query = 'trashed = false and \'' . $folder->getId() . '\' in parents';

        $fileList = $this->service->files->listFiles(array('q' => $query));

        return $fileList;

    }
}

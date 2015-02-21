<?php

namespace GoogleHelper\Drive;

class FilesHelper extends DriveHelper
{

    /**
     * Get a file
     * @param $str_folder_name
     * @param $str_mime_type
     * @param bool $bol_include_trash
     * @param \Google_Service_Drive_DriveFile $obj_parent
     * @param bool $bol_create_if_not_found
     * @return \Google_Service_Drive_DriveFile|null
     */
    public function getFileByName(
       $str_folder_name,
       $str_mime_type,
       $bol_include_trash = false,
       \Google_Service_Drive_DriveFile $obj_parent = null,
       $bol_create_if_not_found = false
    ) {
        $str_query = 'title = \'' . $str_folder_name . '\' and mimeType = \'' . $str_mime_type . '\'';
        if (!$bol_include_trash) {
            $str_query .= ' and trashed = false';
        }
        if ($obj_parent !== null) {
            $str_query .= ' and \'' . $obj_parent->getId() . '\' in parents';
        }
        $obj_file_list = $this->service->files->listFiles(array('q' => $str_query));
        $arr_folders   = $obj_file_list->getItems();
        if (isset($arr_folders[0])) {
            return $arr_folders[0];
        }
        if ($bol_create_if_not_found) {
            return $this->createFolder($str_folder_name, $obj_parent);
        }

        return null;
    }

    /**
     * Get folder
     * @param $str_folder_name
     * @param bool $bol_include_trash
     * @param \Google_Service_Drive_DriveFile $obj_parent
     * @param bool $bol_create_if_not_found
     * @return \Google_Service_Drive_DriveFile|null
     */
    public function getFolderByName(
       $str_folder_name,
       $bol_include_trash = false,
       \Google_Service_Drive_DriveFile $obj_parent = null,
       $bol_create_if_not_found = false
    ) {
        return $this->getFileByName($str_folder_name, self::MIME_FOLDER, $bol_include_trash, $obj_parent,
           $bol_create_if_not_found);
    }

    /**
     * Create a file
     * @param $str_file_name
     * @param $str_mime_type
     * @param \Google_Service_Drive_DriveFile $obj_parent_folder
     * @return \Google_Service_Drive_DriveFile
     */
    public function createFile(
       $str_file_name,
       $str_mime_type,
       \Google_Service_Drive_DriveFile $obj_parent_folder = null
    ) {
        $obj_folder = new \Google_Service_Drive_DriveFile();
        $obj_folder->setTitle($str_file_name);
        $obj_folder->setMimeType($str_mime_type);
        if ($obj_parent_folder !== null) {
            $obj_parent = new \Google_Service_Drive_ParentReference();
            $obj_parent->setId($obj_parent_folder->getId());
            $obj_folder->setParents(array($obj_parent));
        }

        return $this->service->files->insert($obj_folder);
    }

    /**
     * Create a folder
     * @param $str_folder_name
     * @param \Google_Service_Drive_DriveFile $obj_parent_folder
     * @return \Google_Service_Drive_DriveFile
     */
    public function createFolder($str_folder_name, \Google_Service_Drive_DriveFile $obj_parent_folder = null)
    {
        return $this->createFile($str_folder_name, self::MIME_FOLDER, $obj_parent_folder);
    }

    /**
     * Upload a file
     * @param string $str_file_name
     * @param string $str_file_location
     * @param \Google_Service_Drive_DriveFile $obj_folder
     * @return \Google_Service_Drive_DriveFile
     */
    public function uploadFile($str_file_name, $str_file_location, \Google_Service_Drive_DriveFile $obj_folder = null)
    {
        //Insert a file
        $obj_file = new \Google_Service_Drive_DriveFile();
        $obj_file->setTitle($str_file_name);
        $obj_file_info = new \finfo(FILEINFO_MIME_TYPE);
        $str_mime_type = $obj_file_info->file($str_file_location);
        $obj_file->setDescription($str_file_name);
        $obj_file->setMimeType($str_mime_type);
        if ($obj_folder !== null) {
            $obj_parent = new \Google_Service_Drive_ParentReference();
            $obj_parent->setId($obj_folder->getId());
            $obj_file->setParents(array($obj_parent));
        }
        $str_data = file_get_contents($str_file_location);

        return $this->service->files->insert($obj_file, array(
           'data'     => $str_data,
           'mimeType' => $obj_file->getMimeType()
        ));
    }


    /**
     * Download a file from a url
     * @param $str_url
     * @return string|null
     */
    public function downloadFileFromURL($str_url)
    {
        $request     = new \Google_Http_Request($str_url, 'GET', null, null);
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
     * @param \Google_Service_Drive_DriveFile $obj_folder
     * @return \Google_Service_Drive_FileList
     */
    public function getFilesInFolder(\Google_Service_Drive_DriveFile $obj_folder)
    {
        $str_query = 'trashed = false and \'' . $obj_folder->getId() . '\' in parents';

        $obj_file_list = $this->service->files->listFiles(array('q' => $str_query));

        return $obj_file_list;

    }
}

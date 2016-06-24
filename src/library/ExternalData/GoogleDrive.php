<?php

// Refresh the token if it's expired.
// if ($client->isAccessTokenExpired()) {
//     $client->refreshToken($client->getRefreshToken());
//     file_put_contents($credentialsPath, $client->getAccessToken());
// }


require_once("Base.php");

class GoogleDrive extends ExternalDataBase {

    private $client;

    /**
     * Constructor.
     * @param string $applicationName name of the application.
     * @param string $authConfigFile  file location for the config json file.
     */
    public function __construct($applicationName, $authConfigFile)
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName("LikeDrop");
        $this->client->setAuthConfigFile($authConfigFile);
        $this->client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
        $this->client->setAccessType('offline');
    }

    /**
     * Set the auth token
     * @param string $accessToken the json encoded auth object from Google.
     */
    public function setAccessToken($accessToken)
    {
        $this->client->setAccessToken(json_decode($accessToken, true));
    }

    /**
     * Get file list for an authenticated user
     * @link https://developers.google.com/drive/v3/reference/files/list
     *
     * @param  array  $options  options for the file list operation.
     * @return array            array of file objects as stdClass()
     */
    public function listFiles($options)
    {
        $data = [];
        $drive_service = new Google_Service_Drive($this->client);
        $files = $drive_service->files->listFiles($options);
        foreach ($files->getFiles() as $file) {
            $data[] = $file->toSimpleObject();
        }
        return $data;
    }

    /**
     * Get the full metadata for a file
     * @param  string $fileId The google file id to fetch for
     * @return object         object representing the file
     */
    public function getFile($fileId)
    {
        $drive_service = new Google_Service_Drive($this->client);
        $file = $drive_service->files->get($fileId, [
            'fields' => 'appProperties,capabilities,contentHints,createdTime,description,explicitlyTrashed,fileExtension,folderColorRgb,fullFileExtension,headRevisionId,iconLink,id,imageMediaMetadata,isAppAuthorized,kind,lastModifyingUser,md5Checksum,mimeType,modifiedByMeTime,modifiedTime,name,originalFilename,ownedByMe,owners,parents,permissions,properties,quotaBytesUsed,shared,sharedWithMeTime,sharingUser,size,spaces,starred,thumbnailLink,trashed,version,videoMediaMetadata,viewedByMe,viewedByMeTime,viewersCanCopyContent,webContentLink,webViewLink,writersCanShare'
        ]);

        $data = $file->toSimpleObject();
        $data->folder = $this->getFileFolder(null, $file);
        return $data;
    }

    /**
     * Recusrively climb up the parent folder chain
     * @param  [type] $folder the current folder
     * @param  [type] $file   Google Drive file class
     * @return [type]         another level up of the folder tree
     */
    public function getFileFolder($folder, $file)
    {
        if (count($file->getParents()) > 0){

            $drive_service = new Google_Service_Drive($this->client);

            $parents = $file->getParents();
            $parent = $parents[0];

            $file = $drive_service->files->get($parent, [
                'fields' => 'appProperties,capabilities,contentHints,createdTime,description,explicitlyTrashed,fileExtension,folderColorRgb,fullFileExtension,headRevisionId,iconLink,id,imageMediaMetadata,isAppAuthorized,kind,lastModifyingUser,md5Checksum,mimeType,modifiedByMeTime,modifiedTime,name,originalFilename,ownedByMe,owners,parents,permissions,properties,quotaBytesUsed,shared,sharedWithMeTime,sharingUser,size,spaces,starred,thumbnailLink,trashed,version,videoMediaMetadata,viewedByMe,viewedByMeTime,viewersCanCopyContent,webContentLink,webViewLink,writersCanShare'
            ]);

            $folder = "/" . $file->getName() . $folder;
            $folder = $this->getFileFolder($folder, $file);
        }
        return $folder;
    }


}
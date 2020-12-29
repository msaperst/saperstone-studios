<?php

use Google\Exception as ExceptionAlias;

require dirname(__DIR__) . '/resources/autoload.php';

class Gmail {
    /**
     * @var Google_Client
     */
    private $client;
    /**
     * @var Google_Service_Gmail
     */
    private $service;

    /**
     * @var string
     */
    private $id = NULL;

    /**
     * @var Google_Service_Gmail_Message
     */
    private $message;

    /**
     * @var string
     */
    private $user = 'me';

    /**
     * Gmail constructor.
     * @param $subject
     * @throws ExceptionAlias
     */
    public function __construct($subject) {
        $this->client = $this->getClient();
        $this->service = new Google_Service_Gmail($this->client);
        self::setEmail($subject);
        $i = 0;
        while( $this->id == NULL && $i < 60) {
            sleep ( 1 );
            self::setEmail($subject);
            $i++;
        }
    }

    /**
     * Returns an authorized API client.
     * @return Google_Client the authorized client object
     * @throws ExceptionAlias
     * @throws Exception
     */
    function getClient(): Google_Client {
        $client = new Google_Client();
        $client->setApplicationName('Gmail API PHP Quickstart');
        $client->setScopes(Google_Service_Gmail::GMAIL_MODIFY);
        $client->setAuthConfig(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    /**
     * @param $subject
     */
    private function setEmail($subject) {
        $results = $this->service->users_messages->listUsersMessages($this->user, ['maxResults' => 10 ]);
        if (count($results->getMessages()) != 0) {
            foreach ($results->getMessages() as $message) {
                $message = $this->service->users_messages->get($this->user, $message->getId(), ['format' => 'full']);
                $headers = $message->getPayload()->getHeaders();
                foreach ($headers as $header) {
                    if ($header->getName() == 'Subject') {
                        if( $header->getValue() == $subject) {
                            $this->id = $message->getId();
                            $this->message = $this->service->users_messages->get($this->user, $this->id, ['format' => 'full']);
                            return;
                        }
                    }
                }
            }
        }
    }

    /**
     * @return string
     */
    function getEmailHtml(): string {
        $payload = $this->message->getPayload();
        while ($payload->getParts() != NULL) {
            $payload = $payload->getParts();
            if( $payload[0]->getParts() != NULL) {
                $payload = $payload[0];
            } else {
                $payload = $payload[1];
            }
        }
        $sanitizedData = strtr($payload->getBody()->getData(), '-_', '+/');
        return base64_decode($sanitizedData);
    }

    /**
     * @return string
     */
    function getEmailTxt(): string {
        $payload = $this->message->getPayload();
        while ($payload->getParts() != NULL) {
            $payload = $payload->getParts()[0];
        }
        $sanitizedData = strtr($payload->getBody()->getData(), '-_', '+/');
        return base64_decode($sanitizedData);
    }

    /**
     * @return string|null
     */
    function saveAttachment(): ?string {
        $attachmentId = $this->message->getPayload()->getParts()[1]->getBody()->getAttachmentId();
        $attachmentName = $this->message->getPayload()->getParts()[1]->getFilename();
        if ($attachmentId != NULL) {
            $attachmentObj = $this->service->users_messages_attachments->get($this->user, $this->id, $attachmentId);
            $data = $attachmentObj->getData(); //Get data from attachment object
            $data = strtr($data, array('-' => '+', '_' => '/'));
            $myFile = fopen(dirname(__DIR__) . DIRECTORY_SEPARATOR . $attachmentName, "w+");
            fwrite($myFile, base64_decode($data));
            fclose($myFile);
            return dirname(__DIR__) . DIRECTORY_SEPARATOR . $attachmentName;
        }
        return NULL;
    }

    function deleteEmail() {
        $labels = new Google_Service_Gmail_ModifyMessageRequest();
        $labels->setRemoveLabelIds(['UNREAD']);
        $this->service->users_messages->modify($this->user, $this->id, $labels);
        $this->service->users_messages->trash($this->user, $this->id);
    }
}
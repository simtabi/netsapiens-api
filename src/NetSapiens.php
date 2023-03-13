<?php declare(strict_types=1);

namespace Simtabi\NetSapiens;

use Simtabi\NetSapiens\Resources\Auth\OAuth2;
use Simtabi\NetSapiens\Resources\Objects\Address;
use Simtabi\NetSapiens\Resources\Objects\Agent;
use Simtabi\NetSapiens\Resources\Objects\AgentLog;
use Simtabi\NetSapiens\Resources\Objects\AnswerRule;
use Simtabi\NetSapiens\Resources\Objects\Audio;
use Simtabi\NetSapiens\Resources\Objects\Call\Call;
use Simtabi\NetSapiens\Resources\Objects\Call\CallCenterStats;
use Simtabi\NetSapiens\Resources\Objects\Call\CallerIDEmergency;
use Simtabi\NetSapiens\Resources\Objects\Call\CallQueue;
use Simtabi\NetSapiens\Resources\Objects\Call\CallQueueEmailReport;
use Simtabi\NetSapiens\Resources\Objects\Call\CallQueueStats;
use Simtabi\NetSapiens\Resources\Objects\Call\CallRequest;
use Simtabi\NetSapiens\Resources\Objects\CDR2\CDR2;
use Simtabi\NetSapiens\Resources\Objects\CDR2\CDRExport;
use Simtabi\NetSapiens\Resources\Objects\CDR2\CDRSchedule;
use Simtabi\NetSapiens\Resources\Objects\Chart;
use Simtabi\NetSapiens\Resources\Objects\Conference\Conference;
use Simtabi\NetSapiens\Resources\Objects\Conference\ConferenceParticipant;
use Simtabi\NetSapiens\Resources\Objects\Conference\ConferenceRecord;
use Simtabi\NetSapiens\Resources\Objects\Connection;
use Simtabi\NetSapiens\Resources\Objects\Contact;
use Simtabi\NetSapiens\Resources\Objects\Dashboard;
use Simtabi\NetSapiens\Resources\Objects\DefaultReader;
use Simtabi\NetSapiens\Resources\Objects\Department;
use Simtabi\NetSapiens\Resources\Objects\Device\Device;
use Simtabi\NetSapiens\Resources\Objects\Device\DeviceModel;
use Simtabi\NetSapiens\Resources\Objects\Device\DeviceProfile;
use Simtabi\NetSapiens\Resources\Objects\Dial\DialPlan;
use Simtabi\NetSapiens\Resources\Objects\Dial\DialPolicy;
use Simtabi\NetSapiens\Resources\Objects\Dial\DialRule;
use Simtabi\NetSapiens\Resources\Objects\Domain;
use Simtabi\NetSapiens\Resources\Objects\Image;
use Simtabi\NetSapiens\Resources\Objects\MAC;
use Simtabi\NetSapiens\Resources\Objects\Meeting;
use Simtabi\NetSapiens\Resources\Objects\Message\Message;
use Simtabi\NetSapiens\Resources\Objects\Message\MessageSession;
use Simtabi\NetSapiens\Resources\Objects\NDPServer;
use Simtabi\NetSapiens\Resources\Objects\Permission;
use Simtabi\NetSapiens\Resources\Objects\Phone\PhoneConfiguration;
use Simtabi\NetSapiens\Resources\Objects\Phone\PhoneNumber;
use Simtabi\NetSapiens\Resources\Objects\Presence;
use Simtabi\NetSapiens\Resources\Objects\Queued;
use Simtabi\NetSapiens\Resources\Objects\Quota;
use Simtabi\NetSapiens\Resources\Objects\Recording;
use Simtabi\NetSapiens\Resources\Objects\Reseller;
use Simtabi\NetSapiens\Resources\Objects\Route;
use Simtabi\NetSapiens\Resources\Objects\ServerInfo;
use Simtabi\NetSapiens\Resources\Objects\SFU;
use Simtabi\NetSapiens\Resources\Objects\Sites\Site;
use Simtabi\NetSapiens\Resources\Objects\Sites\Sites;
use Simtabi\NetSapiens\Resources\Objects\SMSNumber;
use Simtabi\NetSapiens\Resources\Objects\Subscriber;
use Simtabi\NetSapiens\Resources\Objects\Subscription;
use Simtabi\NetSapiens\Resources\Objects\Time\TimeFrame;
use Simtabi\NetSapiens\Resources\Objects\Time\TimeRange;
use Simtabi\NetSapiens\Resources\Objects\Trace;
use Simtabi\NetSapiens\Resources\Objects\Turn;
use Simtabi\NetSapiens\Resources\Objects\UCInbox;
use Simtabi\NetSapiens\Resources\Objects\UIConfig;
use Simtabi\NetSapiens\Resources\Objects\Upload;
use Simtabi\NetSapiens\Resources\Objects\VoicemailReminders;

class NetSapiens
{

    protected OAuth2 $oAuth2;
    protected array  $guzzleConfig = [];
    protected mixed  $cacher;

    /**
     * Create class instance
     *
     * @version      1.0
     * @since        1.0
     */
    private static self $instance;

    public static function getInstance(string $clientId, string $clientSecret, string $username, string $password, string $baseUrl, callable|null $cacher = null, array $guzzleConfig = []): static
    {
        if (!isset(self::$instance) || is_null(self::$instance)) {
            self::$instance               = new static();
            self::$instance->guzzleConfig = $guzzleConfig;
            self::$instance->oAuth2       = (new OAuth2($baseUrl))
                ->setClientId($clientId)
                ->setClientSecret($clientSecret)
                ->setUsername($username)
                ->setPassword($password);
            self::$instance->cacher       = $cacher;
        }

        return self::$instance;
    }

    private function __construct() {}

    private function __clone() {}

    /**
     * @return OAuth2
     */
    public function getOAuth2(): OAuth2
    {
        return $this->oAuth2;
    }

    // call

    public function getCall(): Call
    {
        return new Call($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getCallCenterStats(): CallCenterStats
    {
        return new CallCenterStats($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getCallerIDEmergency(): CallerIDEmergency
    {
        return new CallerIDEmergency($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getCallQueue(): CallQueue
    {
        return new CallQueue($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getCallQueueEmailReport(): CallQueueEmailReport
    {
        return new CallQueueEmailReport($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getCallQueueStats(): CallQueueStats
    {
        return new CallQueueStats($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getCallRequest(): CallRequest
    {
        return new CallRequest($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    // CDR

    public function getCDR2(): CDR2
    {
        return new CDR2($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getCDRExport(): CDRExport
    {
        return new CDRExport($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getCDRSchedule(): CDRSchedule
    {
        return new CDRSchedule($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }


    //Conference

    public function getConference(): Conference
    {
        return new Conference($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getConferenceParticipant(): ConferenceParticipant
    {
        return new ConferenceParticipant($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getConferenceRecord(): ConferenceRecord
    {
        return new ConferenceRecord($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }


    // device


    public function getDevice(): Device
    {
        return new Device($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getDeviceModel(): DeviceModel
    {
        return new DeviceModel($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getDeviceProfile(): DeviceProfile
    {
        return new DeviceProfile($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    // Dial

    public function getDialPlan(): DialPlan
    {
        return new DialPlan($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getDialPolicy(): DialPolicy
    {
        return new DialPolicy($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getDialRule(): DialRule
    {
        return new DialRule($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    // message

    public function getMessage(): Message
    {
        return new Message($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getMessageSession(): MessageSession
    {
        return new MessageSession($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    // phone

    public function getPhoneConfiguration(): PhoneConfiguration
    {
        return new PhoneConfiguration($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getPhoneNumber(): PhoneNumber
    {
        return new PhoneNumber($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    // sites

    public function getSite(): Site
    {
        return new Site($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getSites(): Sites
    {
        return new Sites($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    // TimeFrame

    public function getTimeFrame(): TimeFrame
    {
        return new TimeFrame($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getTimeRange(): TimeRange
    {
        return new TimeRange($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    // others


    public function getAddress(): Address
    {
        return new Address($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getAgent(): Agent
    {
        return new Agent($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getAgentLog(): AgentLog
    {
        return new AgentLog($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getAnswerRule(): AnswerRule
    {
        return new AnswerRule($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getAudio(): Audio
    {
        return new Audio($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getChart(): Chart
    {
        return new Chart($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getConnection(): Connection
    {
        return new Connection($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getContact(): Contact
    {
        return new Contact($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getDashboard(): Dashboard
    {
        return new Dashboard($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getDefaultReader(): DefaultReader
    {
        return new DefaultReader($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getDepartment(): Department
    {
        return new Department($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getDomain(): Domain
    {
        return new Domain($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getImage(): Image
    {
        return new Image($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getMAC(): MAC
    {
        return new MAC($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getMeeting(): Meeting
    {
        return new Meeting($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getNDPServer(): NDPServer
    {
        return new NDPServer($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getPermission(): Permission
    {
        return new Permission($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getPresence(): Presence
    {
        return new Presence($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getQueued(): Queued
    {
        return new Queued($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getQuota(): Quota
    {
        return new Quota($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getRecording(): Recording
    {
        return new Recording($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getReseller(): Reseller
    {
        return new Reseller($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getRoute(): Route
    {
        return new Route($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getServerInfo(): ServerInfo
    {
        return new ServerInfo($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getSFU(): SFU
    {
        return new SFU($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getSMSNumber(): SMSNumber
    {
        return new SMSNumber($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getSubscriber(): Subscriber
    {
        return new Subscriber($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getSubscription(): Subscription
    {
        return new Subscription($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getTrace(): Trace
    {
        return new Trace($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getTurn(): Turn
    {
        return new Turn($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getUCInbox(): UCInbox
    {
        return new UCInbox($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getUIConfig(): UIConfig
    {
        return new UIConfig($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getUpload(): Upload
    {
        return new Upload($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

    public function getVoicemailReminders(): VoicemailReminders
    {
        return new VoicemailReminders($this->oAuth2, $this->cacher, $this->guzzleConfig);
    }

}
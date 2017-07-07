<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Observer;

use Magento\Cron\Model\ResourceModel\Schedule as ScheduleResource;
use Magento\Cron\Model\Schedule;
use Magento\Cron\Observer\ProcessCronQueueObserver;
use Magento\Framework\App\State;
use Magento\Framework\DB\Adapter\AdapterInterface;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class \Magento\Cron\Test\Unit\Model\ObserverTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ProcessCronQueueObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessCronQueueObserver
     */
    protected $_observer;

    /**
     * @var \Magento\Framework\App\ObjectManager | Mock
     */
    protected $_objectManager;

    /**
     * @var Mock
     */
    protected $_cache;

    /**
     * @var \Magento\Cron\Model\Config | Mock
     */
    protected $_config;

    /**
     * @var \Magento\Cron\Model\ScheduleFactory | Mock
     */
    protected $_scheduleFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface | Mock
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\Console\Request | Mock
     */
    protected $_request;

    /**
     * @var \Magento\Framework\ShellInterface | Mock
     */
    protected $_shell;

    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule\Collection | Mock
     */
    protected $_collection;

    /**
     * @var \Magento\Cron\Model\Groups\Config\Data
     */
    protected $_cronGroupConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTimeMock;

    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $observer;

    /**
     * @var \Psr\Log\LoggerInterface | Mock
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\App\State | Mock
     */
    protected $appStateMock;

    /**
     * @var ScheduleResource | Mock
     */
    private $scheduleResource;

    /**
     * @var AdapterInterface | Mock
     */
    private $connection;

    /**
     * Prepare parameters
     */
    protected function setUp()
    {
        $this->_objectManager = $this->getMockBuilder(
            \Magento\Framework\App\ObjectManager::class
        )->disableOriginalConstructor()->getMock();
        $this->_cache = $this->getMock(\Magento\Framework\App\CacheInterface::class);
        $this->_config = $this->getMockBuilder(
            \Magento\Cron\Model\Config::class
        )->disableOriginalConstructor()->getMock();
        $this->_scopeConfig = $this->getMockBuilder(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->_scopeConfig->expects($this->any())->method('getValue')->will($this->returnValueMap([
            ['system/cron/default/schedule_generate_every', 'store', null, 1],
            ['system/cron/default/schedule_ahead_for', 'store', null, 20],
            ['system/cron/default/schedule_lifetime', 'store', null, 15],
            ['system/cron/default/history_cleanup_every', 'store', null, 10],
            ['system/cron/default/history_success_lifetime', 'store', null, 60],
            ['system/cron/default/history_failure_lifetime', 'store', null, 600],
            ['system/cron/default/use_separate_process', 'store', null, 0],
        ]));

        $this->_collection = $this->getMockBuilder(
            \Magento\Cron\Model\ResourceModel\Schedule\Collection::class
        )->setMethods(
            ['addFieldToFilter', 'load', '__wakeup']
        )->disableOriginalConstructor()->getMock();
        $this->_collection->expects($this->any())->method('addFieldToFilter')->will($this->returnSelf());
        $this->_collection->expects($this->any())->method('load')->will($this->returnSelf());
        $this->_scheduleFactory = $this->getMockBuilder(\Magento\Cron\Model\ScheduleFactory::class)
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->_request = $this->getMockBuilder(\Magento\Framework\App\Console\Request::class)
            ->disableOriginalConstructor()->getMock();
        $this->_shell = $this->getMockBuilder(\Magento\Framework\ShellInterface::class)
            ->disableOriginalConstructor()->setMethods(['execute'])->getMock();
        $this->loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class);

        $this->appStateMock = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = $this->getMock(\Magento\Framework\Event\Observer::class, [], [], '', false);

        $this->dateTimeMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock->expects($this->any())->method('gmtTimestamp')->will($this->returnValue(time()));

        $phpExecutableFinder = $this->getMock(\Symfony\Component\Process\PhpExecutableFinder::class, [], [], '', false);
        $phpExecutableFinder->expects($this->any())->method('find')->willReturn('php');
        $phpExecutableFinderFactory = $this->getMock(
            \Magento\Framework\Process\PhpExecutableFinderFactory::class,
            [],
            [],
            '',
            false
        );
        $phpExecutableFinderFactory->expects($this->any())->method('create')->willReturn($phpExecutableFinder);

        $this->scheduleResource = $this->getMockBuilder(ScheduleResource::class)
            ->disableOriginalConstructor()->getMock();
        $this->connection = $this->getMockBuilder(AdapterInterface::class)->disableOriginalConstructor()->getMock();

        $this->scheduleResource->method('getConnection')->willReturn($this->connection);
        $this->connection->method('delete')->willReturn(1);

        $this->_observer = new ProcessCronQueueObserver(
            $this->_objectManager,
            $this->_scheduleFactory,
            $this->_cache,
            $this->_config,
            $this->_scopeConfig,
            $this->_request,
            $this->_shell,
            $this->dateTimeMock,
            $phpExecutableFinderFactory,
            $this->loggerMock,
            $this->appStateMock
        );
    }

    /**
     * Test case for an empty cron_schedule table and no job generation
     */
    public function testDispatchNoPendingJobs()
    {
        $lastRun = time() + 10000000; // skip cleanup and generation
        $this->_cache->expects($this->any())->method('load')->will($this->returnValue($lastRun));
        $this->_request->expects($this->any())->method('getParam')->will($this->returnValue('default'));

        $this->_config->expects($this->exactly(2))->method('getJobs')
            ->will($this->returnValue(['default' => ['test_job1' => ['test_data']]]));

        $scheduleMock = $this->getMockBuilder(Schedule::class)->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $scheduleMock->expects($this->any())->method('getResource')->will($this->returnValue($this->scheduleResource));
        $scheduleMock->expects($this->never())->method('setStatus');
        $scheduleMock->expects($this->never())->method('setExecutedAt');
        $this->_scheduleFactory->expects($this->exactly(2))->method('create')->will($this->returnValue($scheduleMock));

        $this->_observer->execute($this->observer);
    }

    /**
     * Test case for a cron job in the database that is not found in the config
     */
    public function testDispatchNoJobConfig()
    {
        $lastRun = time() + 10000000; // skip cleanup and generation
        $this->_cache->expects($this->any())->method('load')->will($this->returnValue($lastRun));
        $this->_request->expects($this->any())->method('getParam')->will($this->returnValue('default'));

        $this->_config->expects($this->any())->method('getJobs')->will($this->returnValue(['default' => []]));

        /** @var Schedule | Mock $schedule */
        $schedule = $this->getMock(Schedule::class, ['getJobCode', '__wakeup'], [], '', false);
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('not_existed_job_code'));

        $this->_collection->addItem($schedule);

        $scheduleMock = $this->getMockBuilder(Schedule::class)->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $scheduleMock->expects($this->any())->method('getResource')->will($this->returnValue($this->scheduleResource));
        $scheduleMock->expects($this->never())->method('getScheduledAt');
        $this->_scheduleFactory->expects($this->once())->method('create')->will($this->returnValue($scheduleMock));

        $this->_observer->execute($this->observer);
    }

    /**
     * Test case when a job can't be locked
     */
    public function testDispatchCanNotLock()
    {
        $lastRun = time() + 10000000;  // skip cleanup and generation
        $this->_cache->expects($this->any())->method('load')->will($this->returnValue($lastRun));
        $this->_request->expects($this->any())->method('getParam')->will($this->returnValue('default'));
        /** @var Schedule | Mock $schedule */
        $schedule = $this->getMockBuilder(Schedule::class)
            ->setMethods(['getJobCode', 'tryLockJob', 'getScheduledAt', '__wakeup', 'save'])
            ->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('test_job1'));
        $schedule->expects($this->once())->method('getScheduledAt')->will($this->returnValue('-1 day'));
        $schedule->expects($this->once())->method('tryLockJob')->will($this->returnValue(false));
        $schedule->expects($this->never())->method('setStatus');
        $schedule->expects($this->never())->method('setExecutedAt');
        $abstractModel = $this->getMock(\Magento\Framework\Model\AbstractModel::class, [], [], '', false);
        $schedule->expects($this->any())->method('save')->will($this->returnValue($abstractModel));
        $this->_collection->addItem($schedule);

        $this->_config->expects($this->exactly(2))->method('getJobs')
            ->will($this->returnValue(['default' => ['test_job1' => ['test_data']]]));

        $scheduleMock = $this->getMockBuilder(Schedule::class)->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $scheduleMock->expects($this->any())->method('getResource')->will($this->returnValue($this->scheduleResource));
        $this->_scheduleFactory->expects($this->exactly(2))->method('create')->will($this->returnValue($scheduleMock));

        $this->_observer->execute($this->observer);
    }

    /**
     * Test case for catching the exception 'Too late for the schedule'
     */
    public function testDispatchExceptionTooLate()
    {
        $lastRun = time() + 10000000;  // skip cleanup and generation
        $exceptionMessage = 'Too late for the schedule';
        $scheduleId = 42;
        $jobCode = 'test_job1';
        $exception = $exceptionMessage . ' Schedule Id: ' . $scheduleId . ' Job Code: ' . $jobCode;

        $this->_cache->expects($this->any())->method('load')->willReturn($lastRun);
        $this->_request->expects($this->any())->method('getParam')->willReturn('default');
        /** @var Schedule | Mock $schedule */
        $schedule = $this->getMockBuilder(
            Schedule::class
        )->setMethods(
            [
                'getJobCode',
                'tryLockJob',
                'getScheduledAt',
                'save',
                'setStatus',
                'setMessages',
                '__wakeup',
                'getStatus',
                'getMessages',
                'getScheduleId',
            ]
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->willReturn($jobCode);
        $schedule->expects($this->once())->method('getScheduledAt')->willReturn('-16 minutes');
        $schedule->expects($this->once())->method('tryLockJob')->willReturn(true);
        $schedule->expects($this->once())->method('setStatus')
            ->with($this->equalTo(Schedule::STATUS_MISSED))->willReturnSelf();
        $schedule->expects($this->once())->method('setMessages')->with($this->equalTo($exceptionMessage));
        $schedule->expects($this->once())->method('setStatus')->with(Schedule::STATUS_MISSED);
        $schedule->expects($this->any())->method('getStatus')->willReturn(Schedule::STATUS_MISSED);
        $schedule->expects($this->once())->method('getMessages')->willReturn($exceptionMessage);
        $schedule->expects($this->once())->method('getScheduleId')->willReturn($scheduleId);
        $schedule->expects($this->once())->method('save');
        $this->_collection->addItem($schedule);

        $this->appStateMock->expects($this->once())->method('getMode')->willReturn(State::MODE_DEVELOPER);
        $this->loggerMock->expects($this->once())->method('info')->with($exception);
        $this->_config->expects($this->exactly(2))->method('getJobs')
            ->willReturn(['default' => ['test_job1' => ['test_data']]]);

        $scheduleMock = $this->getMockBuilder(Schedule::class)
            ->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->willReturn($this->_collection);
        $scheduleMock->expects($this->any())->method('getResource')->will($this->returnValue($this->scheduleResource));
        $this->_scheduleFactory->expects($this->exactly(2))->method('create')->willReturn($scheduleMock);

        $this->_observer->execute($this->observer);
    }

    /**
     * Test case catch exception if callback does not exist
     */
    public function testDispatchExceptionNoCallback()
    {
        $lastRun = time() + 10000000;  // skip cleanup and generation
        $exceptionMessage = 'No callbacks found';
        $exception = new \Exception(__($exceptionMessage));
        $jobConfig = ['default' => ['test_job1' => ['instance' => 'Some_Class', /* 'method' => 'not_set' */]]];

        /** @var Schedule | Mock $schedule */
        $schedule = $this->getMockBuilder(
            Schedule::class
        )->setMethods(
            ['getJobCode', 'tryLockJob', 'getScheduledAt', 'save', 'setStatus', 'setMessages', '__wakeup', 'getStatus']
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('test_job1'));
        $schedule->expects($this->once())->method('getScheduledAt')->will($this->returnValue(date('Y-m-d H:i:00')));
        $schedule->expects($this->once())->method('tryLockJob')->will($this->returnValue(true));
        $schedule->expects($this->once())->method('setStatus')
            ->with($this->equalTo(Schedule::STATUS_ERROR))->will($this->returnSelf());
        $schedule->expects($this->once())->method('setMessages')->with($this->equalTo($exceptionMessage));
        $schedule->expects($this->any())->method('getStatus')->willReturn(Schedule::STATUS_ERROR);
        $schedule->expects($this->once())->method('save');
        $this->_request->expects($this->any())->method('getParam')->will($this->returnValue('default'));
        $this->_collection->addItem($schedule);
        $this->loggerMock->expects($this->once())->method('critical')->with($exception);
        $this->_config->expects($this->exactly(2))->method('getJobs')->will($this->returnValue($jobConfig));
        $this->_cache->expects($this->any())->method('load')->will($this->returnValue($lastRun));

        $scheduleMock = $this->getMockBuilder(
            Schedule::class
        )->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $scheduleMock->expects($this->any())->method('getResource')->will($this->returnValue($this->scheduleResource));
        $this->_scheduleFactory->expects($this->exactly(2))->method('create')->will($this->returnValue($scheduleMock));

        $this->_observer->execute($this->observer);
    }

    /**
     * Test case catch exception if callback is not callable or throws exception
     *
     * @param string $cronJobType
     * @param mixed $cronJobObject
     * @param string $exceptionMessage
     * @param int $saveCalls
     * @param \Exception $exception
     *
     * @dataProvider dispatchExceptionInCallbackDataProvider
     */
    public function testDispatchExceptionInCallback(
        $cronJobType,
        $cronJobObject,
        $exceptionMessage,
        $saveCalls,
        $exception
    ) {
        $lastRun = time() + 10000000;  // skip cleanup and generation
        $jobConfig = ['default' => ['test_job1' => ['instance' => $cronJobType, 'method' => 'execute']]];

        $this->_request->expects($this->any())->method('getParam')->will($this->returnValue('default'));
        /** @var Schedule | Mock $schedule */
        $schedule = $this->getMockBuilder(Schedule::class)->setMethods(
            ['getJobCode', 'tryLockJob', 'getScheduledAt', 'save', 'setStatus', 'setMessages', '__wakeup', 'getStatus']
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('test_job1'));
        $schedule->expects($this->once())->method('getScheduledAt')->will($this->returnValue(date('Y-m-d H:i:00')));
        $schedule->expects($this->once())->method('tryLockJob')->will($this->returnValue(true));
        $schedule->expects($this->once())
            ->method('setStatus')
            ->with($this->equalTo(Schedule::STATUS_ERROR))
            ->will($this->returnSelf());
        $schedule->expects($this->once())->method('setMessages')->with($this->equalTo($exceptionMessage));
        $schedule->expects($this->any())->method('getStatus')->willReturn(Schedule::STATUS_ERROR);
        $schedule->expects($this->exactly($saveCalls))->method('save');
        $this->_collection->addItem($schedule);

        $this->loggerMock->expects($this->once())->method('critical')->with($exception);
        $this->_config->expects($this->exactly(2))->method('getJobs')->will($this->returnValue($jobConfig));
        $this->_cache->expects($this->any())->method('load')->will($this->returnValue($lastRun));

        $scheduleMock = $this->getMockBuilder(
            Schedule::class
        )->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $scheduleMock->expects($this->any())->method('getResource')->will($this->returnValue($this->scheduleResource));
        $this->_scheduleFactory->expects($this->exactly(2))->method('create')->will($this->returnValue($scheduleMock));
        $this->_objectManager
            ->expects($this->once())
            ->method('create')
            ->with($this->equalTo($cronJobType))
            ->will($this->returnValue($cronJobObject));

        $this->_observer->execute($this->observer);
    }

    /**
     * @return array
     */
    public function dispatchExceptionInCallbackDataProvider()
    {
        return [
            'non-callable callback' => [
                'Not_Existed_Class',
                '',
                'Invalid callback: Not_Existed_Class::execute can\'t be called',
                1,
                new \Exception(__('Invalid callback: Not_Existed_Class::execute can\'t be called'))
            ],
            'exception in execution' => [
                'CronJobException',
                new \Magento\Cron\Test\Unit\Model\CronJobException(),
                'Test exception',
                2,
                new \Exception(__('Test exception'))
            ],
        ];
    }

    /**
     * Test case, successfully run job
     */
    public function testDispatchRunJob()
    {
        $lastRun = time() + 10000000;  // skip cleanup and generation
        $jobConfig = ['default' => ['test_job1' => ['instance' => 'CronJob', 'method' => 'execute']]];
        $this->_request->expects($this->any())->method('getParam')->will($this->returnValue('default'));

        $scheduleMethods = [
            'getJobCode',
            'tryLockJob',
            'getScheduledAt',
            'save',
            'setStatus',
            'setMessages',
            'setExecutedAt',
            'setFinishedAt',
            '__wakeup',
        ];
        /** @var Schedule | Mock $schedule */
        $schedule = $this->getMockBuilder(
            Schedule::class
        )->setMethods(
            $scheduleMethods
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('test_job1'));
        $schedule->expects($this->once())->method('getScheduledAt')->will($this->returnValue(date('Y-m-d H:i:00')));
        $schedule->expects($this->once())->method('tryLockJob')->will($this->returnValue(true));

        // cron start to execute some job
        $schedule->expects($this->any())->method('setExecutedAt')->will($this->returnSelf());
        $schedule->expects($this->at(5))->method('save');

        // cron end execute some job
        $schedule->expects($this->at(6))
            ->method('setStatus')
            ->with($this->equalTo(Schedule::STATUS_SUCCESS))
            ->will($this->returnSelf());

        $schedule->expects($this->at(8))->method('save');

        $this->_collection->addItem($schedule);
        $this->_config->expects($this->exactly(2))->method('getJobs')->will($this->returnValue($jobConfig));
        $this->_cache->expects($this->any())->method('load')->will($this->returnValue($lastRun));

        $scheduleMock = $this->getMockBuilder(
            Schedule::class
        )->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $scheduleMock->expects($this->any())->method('getResource')->will($this->returnValue($this->scheduleResource));
        $this->_scheduleFactory->expects($this->exactly(2))->method('create')->will($this->returnValue($scheduleMock));

        $testCronJob = $this->getMockBuilder('CronJob')->setMethods(['execute'])->getMock();
        $testCronJob->expects($this->atLeastOnce())->method('execute')->with($schedule);

        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with($this->equalTo('CronJob'))
            ->will($this->returnValue($testCronJob));

        $this->_observer->execute($this->observer);
    }

    /**
     * Testing generate(), iterate over saved cron jobs
     * Generate should not generate any jobs, because they are already in the database
     */
    public function testDispatchNotGenerate()
    {
        $jobConfig = ['default' => [
            'test_job1' => ['instance' => 'CronJob', 'method' => 'execute', 'schedule' => '* * * * *']]
        ];

        $this->_config->expects($this->any())->method('getJobs')->will($this->returnValue($jobConfig));
        $this->_request->expects($this->any())->method('getParam')->will($this->returnValue('default'));
        $this->_cache->expects($this->any())->method('load')->willReturnMap([
            [ProcessCronQueueObserver::CACHE_KEY_LAST_HISTORY_CLEANUP_AT . 'default', time() + 1000], // skip cleanup
            [ProcessCronQueueObserver::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT . 'default', time() - 1000], // do generation
        ]);

        /** @var Schedule | Mock $schedule */
        $schedule = $this->getMockBuilder(Schedule::class)
            ->setMethods(['getJobCode', 'getScheduledAt', '__wakeup', 'save'])
            ->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('test_job1'));
        for ($i=0; $i < 20 * 2; $i+=2) {
            $minutes = 0.5 * $i;
            $schedule->expects($this->at($i))->method('getScheduledAt')
                ->will($this->returnValue(date('Y-m-d H:i:00', strtotime("+$minutes minutes"))));
            $schedule->expects($this->at($i + 1))->method('getScheduledAt')
                ->will($this->returnValue(date('Y-m-d H:i:00', strtotime("+$minutes minutes"))));
            $schedule->expects($this->at($minutes))->method('getId')->willReturn($minutes + 1);
            $this->_collection->addItem($schedule);
        }

        $this->_cache->expects($this->any())->method('save');

        $scheduleMock = $this->getMockBuilder(Schedule::class)
            ->setMethods([
                'getCollection',
                'getResource',
                'trySchedule',
                'save',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $scheduleMock->expects($this->any())->method('getResource')->will($this->returnValue($this->scheduleResource));
        $scheduleMock->expects($this->exactly(20))->method('trySchedule')->willReturn(true);
        $scheduleMock->expects($this->never())->method('save');
        $this->_scheduleFactory->expects($this->any())->method('create')->will($this->returnValue($scheduleMock));

        $this->_observer->execute($this->observer);
    }

    /**
     * Testing generate(), iterate over saved cron jobs and generate jobs
     */
    public function testDispatchGenerate()
    {
        $jobConfig = [
            'default' => [
                'job1' => [
                    'instance' => 'CronJob',
                    'method' => 'execute',
                ],
            ],
        ];

        $jobs = [
            'default' => [
                'job1' => ['config_path' => 'test/path'],
                'job2' => ['schedule' => ''],
                'job3' => ['schedule' => '* * * * *'],
            ],
        ];
        $this->_config->expects($this->at(0))->method('getJobs')->willReturn($jobConfig);
        $this->_config->expects($this->at(1))->method('getJobs')->willReturn($jobs);
        $this->_request->expects($this->any())->method('getParam')->willReturn('default');
        $this->_cache->expects($this->any())->method('load')->willReturnMap([
            [ProcessCronQueueObserver::CACHE_KEY_LAST_HISTORY_CLEANUP_AT . 'default', time() + 1000], // skip cleanup
            [ProcessCronQueueObserver::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT . 'default', time() - 1000], // do generation
        ]);

        /** @var Schedule | Mock $schedule */
        $schedule = $this->getMockBuilder(
            Schedule::class
        )->setMethods(
            ['getJobCode', 'save', 'getScheduledAt', 'unsScheduleId', 'trySchedule', 'getCollection', 'getResource']
        )->disableOriginalConstructor()->getMock();
        $schedule->expects($this->any())->method('getJobCode')->willReturn('job1');
        $schedule->expects($this->exactly(2))->method('getScheduledAt')->willReturn('* * * * *');
        $schedule->expects($this->any())->method('unsScheduleId')->willReturnSelf();
        $schedule->expects($this->any())->method('trySchedule')->willReturnSelf();
        $schedule->expects($this->any())->method('getCollection')->willReturn($this->_collection);
        $schedule->expects($this->atLeastOnce())->method('save')->willReturnSelf();
        $schedule->expects($this->any())->method('getResource')->will($this->returnValue($this->scheduleResource));

        $this->_collection->addItem($schedule);
        $this->_cache->expects($this->any())->method('save');
        $this->_scheduleFactory->expects($this->any())->method('create')->willReturn($schedule);
        $this->_observer->execute($this->observer);
    }

    /**
     * Test case to test the cleanup process
     */
    public function testDispatchCleanup()
    {
        $jobConfig = ['default' => ['test_job1' => ['instance' => 'CronJob', 'method' => 'execute']]];
        $this->_config->expects($this->exactly(2))->method('getJobs')->will($this->returnValue($jobConfig));
        $this->_request->expects($this->any())->method('getParam')->will($this->returnValue('default'));

        $this->_cache->expects($this->any())->method('load')->willReturnMap([
            // do cleanup
            [ProcessCronQueueObserver::CACHE_KEY_LAST_HISTORY_CLEANUP_AT . 'default', time() - 1000],
            // skip generation
            [ProcessCronQueueObserver::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT . 'default', time() + 1000],
        ]);

        $jobs = [
            ['status' => 'success', 'age' => '-61 minutes', 'delete' => true],
            ['status' => 'success', 'age' => '-59 minutes', 'delete' => false],
            ['status' => 'missed', 'age' => '-601 minutes', 'delete' => true],
            ['status' => 'missed', 'age' => '-509 minutes', 'delete' => false],
            ['status' => 'error', 'age' => '-601 minutes', 'delete' => true],
            ['status' => 'error', 'age' => '-509 minutes', 'delete' => false],
        ];

        foreach ($jobs as $job) {
            /** @var Schedule | Mock $schedule */
            $schedule = $this->getMockBuilder(Schedule::class)
                ->disableOriginalConstructor()
                ->setMethods(['getExecutedAt', 'getStatus', 'delete', '__wakeup'])->getMock();
            $schedule->expects($this->any())->method('getExecutedAt')->will($this->returnValue($job['age']));
            $schedule->expects($this->any())->method('getStatus')->will($this->returnValue($job['status']));
            if ($job['delete']) {
                $schedule->expects($this->once())->method('delete');
            } else {
                $schedule->expects($this->never())->method('delete');
            }

            $this->_collection->addItem($schedule);
        }

        $scheduleMock = $this->getMockBuilder(Schedule::class)->disableOriginalConstructor()->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $scheduleMock->expects($this->any())->method('getResource')->will($this->returnValue($this->scheduleResource));
        $this->_scheduleFactory->expects($this->any())->method('create')->will($this->returnValue($scheduleMock));

        $this->_observer->execute($this->observer);
    }

    /**
     * Testing if mismatching schedules will be deleted and
     * if disabled jobs will be deleted
     */
    public function testDispatchRemoveConfigMismatch()
    {
        $jobConfig = ['default' => [
            'test_job1' => ['instance' => 'CronJob', 'method' => 'execute', 'schedule' => '*/10 * * * *'],
            'test_job2' => ['instance' => 'CronJob', 'method' => 'execute', 'schedule' => null]
        ]];
        $this->_config->expects($this->exactly(2))->method('getJobs')->will($this->returnValue($jobConfig));
        $this->_request->expects($this->any())->method('getParam')->will($this->returnValue('default'));

        $this->_cache->expects($this->any())->method('load')->willReturnMap([
            // skip cleanup
            [ProcessCronQueueObserver::CACHE_KEY_LAST_HISTORY_CLEANUP_AT . 'default', time() + 1000],
            // do generation
            [ProcessCronQueueObserver::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT . 'default', time() - 1000],
        ]);

        $jobs = [];
        for ($i = 0; $i<20; $i++) {
            $time = date('Y-m-d H:i:00', strtotime("+$i minutes"));
            $jobs[] = [
                'age' => $time,
                'delete' => !preg_match('#0$#', date('i', strtotime($time)))
            ];
        }

        foreach ($jobs as $job) {
            /** @var Schedule | Mock $schedule */
            $schedule = $this->getMockBuilder(Schedule::class)
                ->disableOriginalConstructor()
                ->setMethods(['getJobCode', 'getScheduledAt', 'getStatus', 'delete', 'save', '__wakeup'])->getMock();
            $schedule->expects($this->any())->method('getStatus')->will($this->returnValue('pending'));
            $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('test_job1'));
            $schedule->expects($this->any())->method('getScheduledAt')->will($this->returnValue($job['age']));
            $this->_collection->addItem($schedule);
        }

        /** @var Schedule | Mock $schedule */
        $schedule = $this->getMockBuilder(Schedule::class)
            ->disableOriginalConstructor()
            ->setMethods(['getJobCode', 'getScheduledAt', 'getStatus', 'delete', 'save', '__wakeup'])->getMock();
        $schedule->expects($this->any())->method('getStatus')->will($this->returnValue('pending'));
        $schedule->expects($this->any())->method('getJobCode')->will($this->returnValue('test_job2'));
        $schedule->expects($this->any())->method('getScheduledAt')->will($this->returnValue(date('Y-m-d H:i:00')));
        $this->_collection->addItem($schedule);

        $scheduleMock = $this->getMockBuilder(Schedule::class)->disableOriginalConstructor()
            ->setMethods(['save', 'getCollection', 'getResource'])->getMock();
        $scheduleMock->expects($this->any())->method('getCollection')->will($this->returnValue($this->_collection));
        $scheduleMock->expects($this->any())->method('getResource')->will($this->returnValue($this->scheduleResource));
        $this->_scheduleFactory->expects($this->any())->method('create')->will($this->returnValue($scheduleMock));

        $query = [
            'status=?' => Schedule::STATUS_PENDING,
            'job_code=?' => 'test_job2',
        ];
        $this->connection->expects($this->at(0))->method('delete')->with(null, $query);

        $scheduledAtList = [];
        foreach ($jobs as $job) {
            if ($job['delete'] === true) {
                $scheduledAtList[] = $job['age'];
            }
        }
        $query = [
            'status=?' => Schedule::STATUS_PENDING,
            'job_code=?' => 'test_job1',
            'scheduled_at in (?)' => $scheduledAtList,
        ];
        $this->connection->expects($this->at(1))->method('delete')->with(null, $query);

        $this->_observer->execute($this->observer);
    }
}

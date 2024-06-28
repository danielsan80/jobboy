<?php

namespace Tests\JobBoy\Flow\Domain\FlowManager;

use Dan\FixtureHandler\FixtureHandler;
use JobBoy\Flow\Domain\FlowManager\DefaultTransitionRegistry;
use JobBoy\Flow\Domain\FlowManager\FlowManager;
use JobBoy\Flow\Domain\FlowManager\Node;
use JobBoy\Flow\Domain\FlowManager\Transition;
use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\Entity\Factory\ProcessFactory;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\Repository\Infrastructure\InMemory\ProcessRepository;
use PHPUnit\Framework\TestCase;

class FlowManagerTest extends TestCase
{

    public function createFixtureHandler(): FixtureHandler
    {
        $processRepository = new ProcessRepository();
        $processFactory = new ProcessFactory(Process::class);

        $transitionRegistry = new DefaultTransitionRegistry();
        $flowManager = new FlowManager($processRepository, $transitionRegistry);

        $process = $processFactory->create(new ProcessData([
            'code' => 'cake'
        ]));
        $processRepository->add($process);


        $fh = new FixtureHandler();
        $fh->setRef('process_repository', $processRepository);
        $fh->setRef('process', $process);
        $fh->setRef('flow_manager', $flowManager);

        $fh->setRef('check_ingredients', Node::create('cake', 'check_ingredients'));
        $fh->setRef('buy_ingredients', Node::create('cake', 'buy_ingredients'));
        $fh->setRef('prepare', Node::create('cake', 'prepare'));
        $fh->setRef('in_oven', Node::create('cake', 'in_oven'));
        $fh->setRef('eat', Node::create('cake', 'eat'));
        $fh->setRef('cleanup', Node::create('cake', 'cleanup'));
        $fh->setRef('emergency', Node::create('cake', 'emergency'));

        $transitionRegistry->add(Transition::createEntry($fh->getRef('check_ingredients')));

        $transitionRegistry->add(Transition::createNodeChange(
            $fh->getRef('check_ingredients'),
            $fh->getRef('prepare'),
            'done'
        ));

        $transitionRegistry->add(Transition::createNodeChange(
            $fh->getRef('check_ingredients'),
            $fh->getRef('buy_ingredients'),
            'missing'
        ));

        $transitionRegistry->add(Transition::createNodeChange(
            $fh->getRef('buy_ingredients'),
            $fh->getRef('check_ingredients'),
            'done'
        ));

        $transitionRegistry->add(Transition::createExit(
            $fh->getRef('buy_ingredients'),
            'unavailable'
        ));

        $transitionRegistry->add(Transition::createNodeChange(
            $fh->getRef('prepare'),
            $fh->getRef('in_oven'),
            'done'
        ));

        $transitionRegistry->add(Transition::createNodeChange(
            $fh->getRef('in_oven'),
            $fh->getRef('emergency'),
            'burned'
        ));


        $transitionRegistry->add(Transition::createNodeChange(
            $fh->getRef('emergency'),
            $fh->getRef('cleanup'),
            'done'
        ));

        $transitionRegistry->add(Transition::createNodeChange(
            $fh->getRef('cleanup'),
            $fh->getRef('prepare'),
            'retry'
        ));


        $transitionRegistry->add(Transition::createExit(
            $fh->getRef('cleanup'),
            'abort'
        ));

        $transitionRegistry->add(Transition::createNodeChange(
            $fh->getRef('in_oven'),
            $fh->getRef('eat'),
            'done'
        ));

        $transitionRegistry->add(Transition::createExit(
            $fh->getRef('eat'),
            'done'
        ));

        return $fh;

    }

    /**
     * @test
     */
    public function not_so_happy_path()
    {
        $fh = $this->createFixtureHandler();

        $flowManager = $fh->getRef('flow_manager');
        $process = $fh->getRef('process');

        $this->assertFlowIsNotWalking($flowManager, $process);
        TestCase::assertFalse($flowManager->atNode($process->id(), 'check_ingredients'));

        $flowManager->reset($process->id());

        $this->assertProcessIsOnNode($flowManager, $process, 'check_ingredients');

        $flowManager->changeNode($process->id(), 'missing');

        $this->assertProcessIsOnNode($flowManager, $process, 'buy_ingredients');

        $flowManager->changeNode($process->id(), 'done');

        $this->assertProcessIsOnNode($flowManager, $process, 'check_ingredients');

        $flowManager->changeNode($process->id(), 'done');

        $this->assertProcessIsOnNode($flowManager, $process, 'prepare');

        $flowManager->changeNode($process->id(), 'done');

        $this->assertProcessIsOnNode($flowManager, $process, 'in_oven');

        $flowManager->changeNode($process->id(), 'burned');

        $this->assertProcessIsOnNode($flowManager, $process, 'emergency');

        $flowManager->changeNode($process->id(), 'done');

        $this->assertProcessIsOnNode($flowManager, $process, 'cleanup');

        $flowManager->changeNode($process->id(), 'retry');

        $this->assertProcessIsOnNode($flowManager, $process, 'prepare');

        $flowManager->changeNode($process->id(), 'done');
        $flowManager->changeNode($process->id(), 'done');

        $this->assertProcessIsOnNode($flowManager, $process, 'eat');

        $flowManager->changeNode($process->id(), 'done');

        $this->assertFlowIsNotWalking($flowManager, $process);

    }


    /**
     * @test
     */
    public function it_throws_an_exception_when_try_to_change_node_on_an_unmapped_transition()
    {
        $fh = $this->createFixtureHandler();

        $flowManager = $fh->getRef('flow_manager');
        $process = $fh->getRef('process');

        $this->assertFlowIsNotWalking($flowManager, $process);
        TestCase::assertFalse($flowManager->atNode($process->id(), 'check_ingredients'));

        $flowManager->reset($process->id());

        $this->assertProcessIsOnNode($flowManager, $process, 'check_ingredients');

        $this->expectExceptionMessage('Is not set a transition for the "cake.check_ingredients" on "expired"');

        $flowManager->changeNode($process->id(), 'expired');

    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_no_entry_transition_is_set()
    {
        $processRepository = new ProcessRepository();
        $processFactory = new ProcessFactory(Process::class);

        $process = $processFactory->create(new ProcessData([
            'code' => 'my_job'
        ]));
        $processRepository->add($process);

        $transitionRegistry = new DefaultTransitionRegistry();
        $flowManager = new FlowManager($processRepository, $transitionRegistry);

        $transitionRegistry->add(Transition::createNodeChange(
            Node::create('my_job', 'do_A'),
            Node::create('my_job', 'do_B'),
            'done'
        ));

        $this->expectExceptionMessage('Is not set an entry transition for the job "my_job"');

        $flowManager->reset($process->id());

    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_try_to_set_two_entry_transition()
    {
        $processRepository = new ProcessRepository();
        $processFactory = new ProcessFactory(Process::class);

        $process = $processFactory->create(new ProcessData([
            'code' => 'my_job'
        ]));
        $processRepository->add($process);

        $transitionRegistry = new DefaultTransitionRegistry();

        $transitionRegistry->add(Transition::createNodeChange(
            Node::create('my_job', 'do_A'),
            Node::create('my_job', 'do_B'),
            'done'
        ));

        $transitionRegistry->add(Transition::createEntry(Node::create('my_job', 'do_A')));

        $this->expectExceptionMessage('The transition "my_job:⚫-->do_B" is an entry but an entry is set yet for job "my_job": "my_job:⚫-->do_A"');

        $transitionRegistry->add(Transition::createEntry(Node::create('my_job', 'do_B')));

    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_try_to_set_the_same_transition()
    {
        $processRepository = new ProcessRepository();
        $processFactory = new ProcessFactory(Process::class);

        $process = $processFactory->create(new ProcessData([
            'code' => 'my_job'
        ]));
        $processRepository->add($process);

        $transitionRegistry = new DefaultTransitionRegistry();

        $transitionRegistry->add(Transition::createNodeChange(
            Node::create('my_job', 'do_A'),
            Node::create('my_job', 'do_B'),
            'done'
        ));

        $transitionRegistry->add(Transition::createEntry(Node::create('my_job', 'do_A')));

        $this->expectExceptionMessage('The transition "my_job:⚫-->do_A" is registered yet');

        $transitionRegistry->add(Transition::createEntry(Node::create('my_job', 'do_A')));

    }


    protected function assertFlowIsNotWalking(FlowManager $flowManager, $process): void
    {
        TestCase::assertNull($process->get('node'));
        TestCase::assertFalse($flowManager->isWalking($process->id()));
    }

    protected function assertProcessIsOnNode(FlowManager $flowManager, $process, string $expectedNode): void
    {
        TestCase::assertEquals($expectedNode, $process->get('node'));
        TestCase::assertTrue($flowManager->isWalking($process->id()));
        TestCase::assertTrue($flowManager->atNode($process->id(), $expectedNode));
    }

}
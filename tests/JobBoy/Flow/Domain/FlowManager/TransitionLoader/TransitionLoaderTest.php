<?php

namespace Tests\JobBoy\Flow\Domain\FlowManager\TransitionLoader;

use JobBoy\Flow\Domain\FlowManager\Node;
use JobBoy\Flow\Domain\FlowManager\Transition as RealTransition;
use JobBoy\Flow\Domain\FlowManager\TransitionLoader\Job;
use JobBoy\Flow\Domain\FlowManager\TransitionLoader\Transition;
use JobBoy\Flow\Domain\FlowManager\TransitionLoader\TransitionLoader;
use JobBoy\Flow\Domain\FlowManager\TransitionLoader\TransitionSet;
use JobBoy\Flow\Domain\FlowManager\TransitionRegistry;
use PHPUnit\Framework\TestCase;

class TransitionLoaderTest extends TestCase
{

    /**
     * @test
     */
    public function it_works(): void
    {
        $expectedTransitionRegistry = new TransitionRegistry();

        $expectedTransitionRegistry->add(RealTransition::createEntry(Node::create('cake', 'check_ingredients')));
        $expectedTransitionRegistry->add(RealTransition::createNodeChange(
            Node::create('cake', 'check_ingredients'),
            Node::create('cake', 'prepare'),
            'done'
        ));
        $expectedTransitionRegistry->add(RealTransition::createNodeChange(
            Node::create('cake', 'prepare'),
            Node::create('cake', 'eat'),
            'done'
        ));
        $expectedTransitionRegistry->add(RealTransition::createExit(
            Node::create('cake', 'eat'),
            'done'
        ));

        $expectedTransitionRegistry->add(RealTransition::createEntry(Node::create('day', 'wakeup')));
        $expectedTransitionRegistry->add(RealTransition::createNodeChange(
            Node::create('day', 'wakeup'),
            Node::create('day', 'work'),
            'done'
        ));
        $expectedTransitionRegistry->add(RealTransition::createNodeChange(
            Node::create('day', 'work'),
            Node::create('day', 'sleep'),
            'done'
        ));
        $expectedTransitionRegistry->add(RealTransition::createExit(
            Node::create('day', 'sleep'),
            'done'
        ));

        $transitionRegistry = new TransitionRegistry();

        $transitionLoader = new TransitionLoader($transitionRegistry);

        $transitionLoader->load(new TransitionSet(
                'cake', [
                Transition::entry('check_ingredients'),
                Transition::nodeChange('check_ingredients', 'prepare', 'done'),
                Transition::nodeChange('prepare', 'eat', 'done'),
                Transition::exit('eat', 'done'),

            ])
        );

        $transitionLoader->load(new TransitionSet(
                'day', [
                Transition::entry('wakeup'),
                Transition::nodeChange('wakeup', 'work', 'done'),
                Transition::nodeChange('work', 'sleep', 'done'),
                Transition::exit('sleep', 'done'),
            ])
        );


        $this->assertEquals($expectedTransitionRegistry, $transitionRegistry);


    }

}

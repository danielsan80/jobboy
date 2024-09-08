<?php
declare(strict_types=1);

namespace Tests\JobBoy\Flow\Domain\FlowManager\JobSchema\Builder;

use JobBoy\Flow\Domain\FlowManager\JobSchema\Builder\JobSchemaBuilder;
use JobBoy\Flow\Domain\FlowManager\JobSchema\JobSchema;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Event\Event;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Job\Job;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State\State;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\State\StateCode;
use JobBoy\Flow\Domain\FlowManager\JobSchema\Model\Transition\Transition;
use PHPUnit\Framework\TestCase;

class JobSchemaBuilderTest extends TestCase
{

    /** @test */
    public function it_works1()
    {
        $stateDiagram = JobSchemaBuilder::create()
            ->setJob('my_job', 'My Job')
            ->createState('state1')->asEntry()
            ->createState('state2')->whichComesFrom('state1', 'done')->open()
            ->createState('state2.1')->asEntry()->open()
            ->createState('state2.1.1')->asEntry()
            ->createState('state2.1.2')->whichComesFrom('state2.1.1', 'done')
            ->createState('state2.1.3')->whichComesFrom('state2.1.2', 'done')->asExit('done')
            ->close()
            ->createState('state2.2')->whichComesFrom('state2.1', 'done')
            ->createState('state2.3')->whichComesFrom('state2.2', 'done')->asExit('done')
            ->close()
            ->createState('state3')->whichComesFrom('state2', 'done')->asExit('done')
            ->build();

        $expected = JobSchema::create((Job::fromString('my_job', 'My Job')))
            ->addState(State::fromString('state1'))
            ->addState(State::fromString('state2'))
            ->addState(State::fromString('state2.1')->setParent(StateCode::create('state2')))
            ->addState(State::fromString('state2.1.1')->setParent(StateCode::create('state2.1')))
            ->addState(State::fromString('state2.1.2')->setParent(StateCode::create('state2.1')))
            ->addState(State::fromString('state2.1.3')->setParent(StateCode::create('state2.1')))
            ->addState(State::fromString('state2.2')->setParent(StateCode::create('state2')))
            ->addState(State::fromString('state2.3')->setParent(StateCode::create('state2')))
            ->addState(State::fromString('state3'))
            ->addTransition(Transition::entry(StateCode::create('state1')))
            ->addTransition(Transition::entry(StateCode::create('state2.1')))
            ->addTransition(Transition::entry(StateCode::create('state2.1.1')))
            ->addTransition(Transition::exit(StateCode::create('state2.1.3'), Event::fromString('done')))
            ->addTransition(Transition::exit(StateCode::create('state2.3'), Event::fromString('done')))
            ->addTransition(Transition::exit(StateCode::create('state3'), Event::fromString('done')))
            ->addTransition(Transition::change(StateCode::create('state1'), StateCode::create('state2'), Event::fromString('done')))
            ->addTransition(Transition::change(StateCode::create('state2'), StateCode::create('state3'), Event::fromString('done')))
            ->addTransition(Transition::change(StateCode::create('state2.1'), StateCode::create('state2.2'), Event::fromString('done')))
            ->addTransition(Transition::change(StateCode::create('state2.2'), StateCode::create('state2.3'), Event::fromString('done')))
            ->addTransition(Transition::change(StateCode::create('state2.1.1'), StateCode::create('state2.1.2'), Event::fromString('done')))
            ->addTransition(Transition::change(StateCode::create('state2.1.2'), StateCode::create('state2.1.3'), Event::fromString('done')));


//        echo $expected->toPlantUml();
//
//        var_dump(strtr(
//            'http://www.plantuml.com/plantuml/png/{{ schema }}',
//            [
//                '{{ schema }}' => encodep($expected->toPlantUml()),
//            ]
//        ));

        $this->assertEquals($expected, $stateDiagram);
    }

}

<?php
declare(strict_types=1);

namespace Tests\JobBoy\Flow\Domain\FlowManager\StateDiagram\Builder;

use JobBoy\Flow\Domain\FlowManager\StateDiagram\Builder\StateDiagramBuilder;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Event\Event;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Event\EventCode;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Job\Job;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Job\JobCode;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State\State;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\State\StateCode;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\Model\Transition\Transition;
use JobBoy\Flow\Domain\FlowManager\StateDiagram\StateDiagram;
use PHPUnit\Framework\TestCase;
use function Jawira\PlantUml\encodep;

class StateDiagramBuilderTest extends TestCase
{

    /** @test */
    public function it_works1()
    {
        $stateDiagram = StateDiagramBuilder::create()
            ->setJob('my_job', 'My Job')
            ->createState('state1')->asEntry()
            ->createState('state2')->whichComesFrom('state1', 'done')->open()
                ->createState('state2.1')->asEntry()->open()
                    ->createState('state2.1.1')->asEntry()
                    ->createState('state2.1.2')->whichComesFrom('state2.1.1', 'done')
                    ->createState('state2.1.3')->whichComesFrom('state2.1.2', 'done')->asExit('done')
                    ->close()
                ->createState('state2.2')->whichComesFrom('state2.1', 'done')
                ->createState('state2.3')->whichComesFrom('state2.2','done')->asExit('done')
                ->close()
            ->createState('state3')->whichComesFrom('state2', 'done')->asExit('done')
            ->build();

        $expected = StateDiagram::create((Job::fromString('my_job', 'My Job')))
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
            ->addTransition(Transition::change(StateCode::create('state2.1.2'), StateCode::create('state2.1.3'), Event::fromString('done')))
        ;


        echo $expected->toPlantUml();

        var_dump(strtr(
            'http://www.plantuml.com/plantuml/png/{{ schema }}',
            [
                '{{ schema }}' => encodep($expected->toPlantUml()),
            ]
        ));

        $this->assertEquals($expected, $stateDiagram);
    }


//    /** @test */
//    public function it_works()
//    {
//        $stateDiagram = StateDiagramBuilder::create()
//            ->createJob('public_data', 'Public Data')
//            ->createState('start', 'Start')
//            ->createStateBuilder('setup', 'Setup')
//                ->createChild('start_setup', 'Start Setup')
//                ->createChild('select_public_db', 'Select Public DB')
//                ->createChild('setup_working_db', 'Setup Working DB')
//                ->createChild('wait_working_db_setup', 'Wait Working DB Setup')
//                ->createChild('setup_working_db_failed', 'Setup Working DB Failed')
//                ->createChild('end_setup', 'Setup End')
//                ->setEntry('start_setup')
//                ->setExit('end_setup')
//                ->createThread([
//                    'start_setup',
//                    'select_public_db',
//                    'setup_working_db',
//                    'wait_working_db_setup',
//                    'setup_working_db_failed',
//                    'end_setup'
//                ], 'done')
//            ->build()
//            ->createStateBuilder('asset', 'Asset')
//                ->addChild('start_asset', 'Start Asset')
//                ->addChild('add_all_asset_types_to_remove_list', 'Add all AssetTypes to RemoveList')
//                ->addChild('copy_asset_types', 'Copy AssetTypes')
//                ->addChild('remove_old_asset_types', 'Remove Old AssetTypes')
//                ->addChild('add_all_asset_classes_to_remove_list', 'Add all AssetClasses to RemoveList')
//                ->addChild('copy_asset_classes', 'Copy AssetClasses')
//                ->addChild('remove_old_asset_classes', 'Remove Old AssetClasses')
//                ->addChild('end_asset', 'End Asset')
//                ->setEntry('start_asset')
//                ->setExit('end_asset')
//                ->createThread([
//                    'start_asset',
//                    'add_all_asset_types_to_remove_list',
//                    'copy_asset_types',
//                    'remove_old_asset_types',
//                    'add_all_asset_classes_to_remove_list',
//                    'copy_asset_classes',
//                    'remove_old_asset_classes',
//                    'end_asset'
//                ], 'done')
//            ->build()
//            ->createState('end', 'End')
//            ->setEntry('start')
//            ->setExit('end')
//            ->createThread([
//                'start',
//                'setup',
//                'asset',
//                'end'
//            ], 'done')
//        ->build();
//
//        $this->assertEquals('', (string)$stateDiagram);
//
//    }

}

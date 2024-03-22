<?php
declare(strict_types=1);

namespace JobBoy\Flow\Domain\FlowManager\StateDiagram\Builder;

use JobBoy\Flow\Domain\FlowManager\FlowSchema\Builder\StateDiagramBuilder;
use PHPUnit\Framework\TestCase;

class StateDiagramBuilderTest extends TestCase
{

    /** @test */
    public function it_works()
    {
        $stateDiagram = StateDiagramBuilder::create()
            ->createJob('public_data', 'Public Data')
            ->createState('start', 'Start')
            ->createStateBuilder('setup', 'Setup')
                ->addChild('start_setup', 'Start Setup')
                ->addChild('select_public_db', 'Select Public DB')
                ->addChild('setup_working_db', 'Setup Working DB')
                ->addChild('wait_working_db_setup', 'Wait Working DB Setup')
                ->addChild('setup_working_db_failed', 'Setup Working DB Failed')
                ->addChild('end_setup', 'Setup End')
                ->setEntry('start_setup')
                ->setExit('end_setup')
                ->createThread([
                    'start_setup',
                    'select_public_db',
                    'setup_working_db',
                    'wait_working_db_setup',
                    'setup_working_db_failed',
                    'end_setup'
                ], 'done')
            ->build()
            ->createStateBuilder('asset', 'Asset')
                ->addChild('start_asset', 'Start Asset')
                ->addChild('add_all_asset_types_to_remove_list', 'Add all AssetTypes to RemoveList')
                ->addChild('copy_asset_types', 'Copy AssetTypes')
                ->addChild('remove_old_asset_types', 'Remove Old AssetTypes')
                ->addChild('add_all_asset_classes_to_remove_list', 'Add all AssetClasses to RemoveList')
                ->addChild('copy_asset_classes', 'Copy AssetClasses')
                ->addChild('remove_old_asset_classes', 'Remove Old AssetClasses')
                ->addChild('end_asset', 'End Asset')
                ->setEntry('start_asset')
                ->setExit('end_asset')
                ->createThread([
                    'start_asset',
                    'add_all_asset_types_to_remove_list',
                    'copy_asset_types',
                    'remove_old_asset_types',
                    'add_all_asset_classes_to_remove_list',
                    'copy_asset_classes',
                    'remove_old_asset_classes',
                    'end_asset'
                ], 'done')
            ->build()
            ->createState('end', 'End')
            ->setEntry('start')
            ->setExit('end')
            ->createThread([
                'start',
                'setup',
                'asset',
                'end'
            ], 'done')
        ->build();

        $this->assertEquals('', (string)$stateDiagram);

    }

}

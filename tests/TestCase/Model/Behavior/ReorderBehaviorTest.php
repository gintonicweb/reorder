<?php
namespace Reorder\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Reorder\Model\Behavior\ReorderBehavior;

/**
 * Reorder\Model\Behavior\reorderBehavior Test Case
 */
class ReorderBehaviorTest extends TestCase
{
    public $fixtures = [
        'plugin.Reorder.Songs',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Songs = TableRegistry::get('Reorder.Songs');
        $this->Songs->addBehavior('Reorder.Reorder', ['field' => 'play_order']);
        $this->Behavior = $this->Songs->behaviors()->Reorder;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
        unset($this->Behavior, $this->Songs);
    }

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testInitialization()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test the update of an existing item
     *
     * @return void
     */
    public function testBeforeSaveOnReorder()
    {
        $song = $this->Songs->get(1);
        $song->play_order = 2;
        $this->Songs->save($song);

        $result = $this->Songs->find('list', ['valueField' => 'play_order'])->toArray();
        $expected = [
            1 => 2,
            2 => 1,
            3 => 3,
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test the insertion of a new item
     *
     * @return void
     */
    public function testBeforeSaveOnInsert()
    {
        $song = $this->Songs->newEntity([
            'title' => 'New Song',
            'play_order' => 2,
        ]);
        $this->Songs->save($song);
        
        $result = $this->Songs->find('list', ['valueField' => 'play_order'])->toArray();
        $expected = [
            1 => 1,
            2 => 3,
            3 => 4,
            4 => 2,
        ];
        $this->assertEquals($expected, $result);
    }
    
    /**
     * Test the removal of an existing item
     *
     * @return void
     */
    public function testBeforeDelete()
    {
        $song = $this->Songs->get(1);
        $this->Songs->delete($song);
        
        $result = $this->Songs->find('list', ['valueField' => 'play_order'])->toArray();
        $expected = [
            2 => 1,
            3 => 2,
        ];
        $this->assertEquals($expected, $result);
    }
}

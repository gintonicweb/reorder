<?php
namespace Reorder\Model\Behavior;

use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\Query;
use Cake\Database\Expression\QueryExpression;

/**
 * Reordering behavior
 */
class ReorderBehavior extends Behavior
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'allowGap' => true, // todo: always allow gap for now
        'field' => null
    ];

    /**
     * This event is fired before each entity is saved
     *
     * @param \Cake\Event\Event $event The event data
     * @param \Cake\Datasource\EntityInterface $entity The entity being saved
     * @return void
     */
    public function beforeSave(Event $event, Entity $entity)
    {
        $config = $this->config();
        if (!$entity->dirty($config['field'])) {
            return;
        }
        
        $newPos = $entity->$config['field'];
        
        $oldEntity = $this->getOldEntity($entity);
        if (is_null($oldEntity)) {
            // Adding new entity
            $oldPos = null;
        } else {
            $oldPos = $oldEntity->$config['field'];
        }
        
        list($conditions, $expression) = $this->getQueryData($oldPos, $newPos);
        $this->reorder($conditions, $expression);
    }
    
    /**
     * This event is fired before an entity is deleted.
     *
     * @param \Cake\Event\Event $event The event data
     * @param \Cake\Datasource\EntityInterface $entity The entity being saved
     * @return void
     */
    public function beforeDelete(Event $event, Entity $entity)
    {
        $config = $this->config();
        
        $newPos = null;
        $oldPos = $entity->$config['field'];
        
        list($conditions, $expression) = $this->getQueryData($oldPos, $newPos);
        $this->reorder($conditions, $expression);
    }


    /**
     * Query the database to obtain the data of an previously existing entity. 
     *
     * @param \Cake\Datasource\EntityInterface $entity The modified entity.
     * @return \Cake\Datasource\EntityInterface
     */
    public function getOldEntity(Entity $entity)
    {
        $config = $this->config();
        $primaryKey = $this->_table->primaryKey();
        $conditions = [$primaryKey => $entity->$primaryKey];
        
        return $this->_table->find()
            ->select([$config['field']])
            ->where($conditions)
            ->first();
    }
    
    /**
     * Obtain the query conditions and expression for the reorder based on
     *  the old and new positions. 
     *
     * @param int $oldPos The old position.
     * @param int $newPos The new position.
     * @return array
     */
    public function getQueryData($oldPos, $newPos)
    {
        $field = $this->config()['field'];
        if (is_null($newPos)) {
            // Deleting
            $conditions = [$field . ' >' => $oldPos];
            $expression = new QueryExpression($field . ' = ' . $field . ' - 1');
        } else if (is_null($oldPos)) { 
            // Adding
            $conditions = [$field . ' >=' => $newPos];
            $expression = new QueryExpression($field . ' = ' . $field . ' + 1');
        } else if ($newPos > $oldPos) {
            // Updating range
            $conditions = [
                $field . ' >' => $oldPos, 
                $field . ' <=' => $newPos
            ];
            $expression = new QueryExpression($field . ' = ' . $field . ' - 1');
        } else {
            // Updating range
            $conditions = [
                $field . ' <' => $oldPos, 
                $field . ' >=' => $newPos
            ];
            $expression = new QueryExpression($field . ' = ' . $field . ' + 1');
        }
        
        return [$conditions, $expression];
    }

    /**
     * Process to the reorder of the selected field. The entity matching the conditions
     *  will be either incremented on decremented based on the given expression.
     *
     * @param array $conditions The query conditions.
     * @param Cake\Database\ExpressionInterface $expression The query expression.
     * @return array
     */
    public function reorder(array $conditions, $expression)
    {
        $this->_table->query()
            ->update()
            ->set($expression)
            ->where($conditions)
            ->execute();
    }
}

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
    
    public function beforeDelete(Event $event, Entity $entity)
    {
        $config = $this->config();
        
        $newPos = null;
        $oldPos = $entity->$config['field'];
        
        list($conditions, $expression) = $this->getQueryData($oldPos, $newPos);
        
        $this->reorder($conditions, $expression);
    }


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

    public function reorder($conditions, $expression)
    {
        $this->_table->query()
            ->update()
            ->set($expression)
            ->where($conditions)
            ->execute();
    }
}

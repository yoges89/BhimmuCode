<?php

declare(strict_types=1);

namespace Drupal\bhimmu_mongodb\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\mongodb\DatabaseFactory;
use Drupal\mongodb\MongoDb;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Bhimmu MongoDB routes.
 */
final class BhimmuMongodbController extends ControllerBase {

  /**
   * The controller constructor.
   */
  public function __construct(
    private readonly DatabaseFactory $mongodbDatabaseFactory,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('mongodb.database_factory'),
    );
  }

  /**
   * Builds the response.
   */
  public function taskList(): array {

    // Make header row.
    $header = [
      'id' => $this->t('ID'),
      'title' => $this->t('Title'),
      'is_completed' => $this->t('Is Completed'),
      'remark' => $this->t('Remark'),
      'actions' => $this->t('Actions'),
    ];

    // Invoke MongoDB instance.
    $database = $this->mongodbDatabaseFactory->get('default');

    // Fetch documents from a collection.
    /** @var \MongoDB\Model\BSONDocument $task */
    foreach ($database->Tasks->find() as $task) {
      $task = $task->jsonSerialize();
      $tasks[] = [
        'id' => [
          'data' => [
            '#markup' => $task->_id,
          ],
        ],
        'title' => [
          'data' => [
            '#markup' => $task->taskName,
          ],
        ],
        'is_completed' => [
          'data' => [
            '#markup' => $task->isComplete ? 'Yes': 'No',
          ],
        ],
        'remark' => [
          'data' => [
            '#markup' => $task->remark,
          ],
        ],
        'actions' => [
          'data' => [
            '#type' => 'operations',
            '#links' => [
              'view' => [
                'title' => $this->t('<i class="fa-solid fa-square-rss"></i> View'),
                'url' => Url::fromRoute('bhimmu_mongodb.crud.view', ['task_id' => $task->_id]),
              ],
              'edit' => [
                'title' => $this->t('<i class="fa-solid fa-square-rss"></i> Edit'),
                'url' => Url::fromRoute('bhimmu_mongodb.crud.edit', ['task_id' => $task->_id]),
              ],
              'delete' => [
                'title' => $this->t('<i class="fa-solid fa-square-rss"></i> Delete'),
                'url' => Url::fromRoute('bhimmu_mongodb.crud.delete', ['task_id' => $task->_id]),
              ],
            ],
          ],
        ],
      ];
    }

    $build['content'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $tasks,
      '#empty' => $this->t('There is no tasks!'),
    ];

    return $build;
  }

  public function getTask(string $task_id) {
    $database = $this->mongodbDatabaseFactory->get('default');
    /** @var \MongoDB\Model\BSONDocument $task */
    $task = $database->Tasks->findOne(['_id' => new \MongoDB\BSON\ObjectID($task_id)]);
    $task = $task->jsonSerialize();
    return [
      '#theme' => 'task',
      '#task' => $task,
    ];
  }

}

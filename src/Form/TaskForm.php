<?php

declare(strict_types=1);

namespace Drupal\bhimmu_mongodb\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mongodb\DatabaseFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a Bhimmu MongoDB form.
 */
final class TaskForm extends FormBase {

  /**
   * MongoDB database factory.
   *
   * @var \Drupal\mongodb\DatabaseFactory
   */
  protected $mongodbDatabaseFactory;

  /**
   * Symfony\Component\HttpFoundation\RequestStack
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Constructor function.
   *
   * @param \Drupal\mongodb\DatabaseFactory $mongoDB
   */
  public function __construct(DatabaseFactory $mongoDB, RequestStack $request) {
    $this->mongodbDatabaseFactory = $mongoDB;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('mongodb.database_factory'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'bhimmu_mongodb_task';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $task = NULL;
    $task_id = $this->request->getCurrentRequest()->get('task_id');
    if (!empty($task_id)) {
      $database = $this->mongodbDatabaseFactory->get('default');
      /** @var \MongoDB\Model\BSONDocument $task */
      $task = $database->Tasks->findOne(['_id' => new \MongoDB\BSON\ObjectID($task_id)]);
      $task = $task->jsonSerialize();
    }
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
      '#default_value' => $task?->taskName,
    ];
    $form['remark'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Remark'),
      '#required' => TRUE,
      '#default_value' => $task?->remark,
    ];
    $form['is_completed'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Is completed'),
      '#default_value' => $task?->isComplete,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save task'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
    //   if (mb_strlen($form_state->getValue('message')) < 10) {
    //     $form_state->setErrorByName(
    //       'message',
    //       $this->t('Message should be at least 10 characters.'),
    //     );
    //   }
    // @endcode
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $task_id = $task_id = $this->request->getCurrentRequest()->get('task_id');
    $collection = $this->mongodbDatabaseFactory->get('default')
        ->selectCollection('Tasks');
    if (!empty($task_id)) {
      $updateResult = $collection->updateOne(
        [
          '_id' => new \MongoDB\BSON\ObjectID($task_id),
        ],
        ['$set' => ['remark' => $form_state->getValue('remark'),],]);
      $this->messenger()->addStatus($this->t('The task has been updated.'));
    }
    else {
      $collection->insertOne([
        'taskName' => $form_state->getValue('title'),
        'remark' => $form_state->getValue('remark'),
        'isComplete' => $form_state->getValue('is_completed'),
      ]);
      $this->messenger()->addStatus($this->t('The task has been created.'));
    }

    $form_state->setRedirect('bhimmu_mongodb.crud');
  }

}

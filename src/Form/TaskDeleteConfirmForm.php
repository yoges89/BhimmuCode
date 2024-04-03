<?php

declare(strict_types=1);

namespace Drupal\bhimmu_mongodb\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\mongodb\DatabaseFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @todo Add a description for the form.
 */
final class TaskDeleteConfirmForm extends ConfirmFormBase {

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
    return 'bhimmu_mongodb_task_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion(): TranslatableMarkup {
    return $this->t('Are you sure you want to do this?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl(): Url {
    return new Url('bhimmu_mongodb.crud');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // @todo Place your code here.
    $task_id = $task_id = $this->request->getCurrentRequest()->get('task_id');
    $collection = $this->mongodbDatabaseFactory->get('default')
        ->selectCollection('Tasks');
    $collection->deleteOne(['_id' => new \MongoDB\BSON\ObjectID($task_id)]);
    $this->messenger()->addStatus($this->t('Task has been deleted!'));
    $form_state->setRedirectUrl(new Url('bhimmu_mongodb.crud'));
  }

}

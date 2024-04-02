<?php

declare(strict_types=1);

namespace Drupal\bhimmu_mongodb\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mongodb\DatabaseFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * Constructor function.
   *
   * @param \Drupal\mongodb\DatabaseFactory $mongoDB
   */
  public function __construct(DatabaseFactory $mongoDB) {
    $this->mongodbDatabaseFactory = $mongoDB;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('mongodb.database_factory'));
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

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
    ];
    $form['remark'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Remark'),
      '#required' => TRUE,
    ];
    $form['is_completed'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Is completed'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Send'),
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
    $this->messenger()->addStatus($this->t('The message has been sent.'));
    $collection = $this->mongodbDatabaseFactory->get('default')
      ->selectCollection('Tasks');
    $collection->insertOne([
      'taskName' => $form_state->getValue('title'),
      'remark' => $form_state->getValue('remark'),
      'isComplete' => $form_state->getValue('is_completed'),
    ]);
    $form_state->setRedirect('bhimmu_mongodb.crud');
  }

}

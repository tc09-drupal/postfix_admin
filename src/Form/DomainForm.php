<?php
/**
 * @file
 * Contains \Drupal\postfix_admin\Form\DomainForm
 */
namespace Drupal\postfix_admin\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an Postfix Domain Form
 */
class DomainForm extends FormBase {
  /**
   * (@inheritdoc)
   */
  public function getFormId() {
    return 'postfix_admin_domain_form';
  }
  /**
   * (@inheritdoc)
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['domain'] = array(
      '#title' => t('Domain Name'),
      '#type' => 'textfield',
      '#size' => 25,
      '#description' => t("Email Domain Name to be supported"),
      '#required' => TRUE,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
    );
    return $form;
  }
  /**
   * (@inheritdoc)
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('domain');
    $select = Database::getConnection()->select('postfix_domain', 'r');
    $select->fields('r', array('domain'));
    $select->condition('domain', $value);  
    $results = $select->execute();
    if (!empty($results->fetchCol())) {
      // we found a row with this nid and email
      $form_state->setErrorByName('domain',
        t('The domain %domain is already in the list.',
        array('%domain' => $value))
      );
    }
  }
  /**
   * (@inheritdoc)
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    db_insert('postfix_domain')
      ->fields(array(
        'domain' => $form_state->getValue('domain'),
      ))->execute();
    drupal_set_message(t('domain added'));
  }
}

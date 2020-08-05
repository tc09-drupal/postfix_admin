<?php
/**
 * @file
 * Contains \Drupal\postfix_admin\Form\AliasForm
 */
namespace Drupal\postfix_admin\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an Postfix Domain Form
 */
class AliasForm extends FormBase {
  /**
   * (@inheritdoc)
   */
  public function getFormId() {
    return 'postfix_admin_alias_form';
  }
  /**
   * (@inheritdoc)
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['email'] = array(
      '#title' => t('Email Address'),
      '#type' => 'textfield',
      '#size' => 50,
      '#description' => t("Email Address"),
      '#required' => TRUE,
    );
    $form['alias'] = array(
      '#title' => t('Alias Name'),
      '#type' => 'textfield',
      '#size' => 25,
      '#description' => t("Email Alias Name"),
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
    $email = $form_state->getValue('email');
    $alias = $form_state->getValue('alias');
    $select = Database::getConnection()->select('postfix_alias', 'r');
    $select->fields('r', array('id'));
    $select->condition('source', $email);  
    $results = $select->execute();
    if (!empty($results->fetchCol())) {
      // we found a row with this email and alias
      $form_state->setErrorByName('alias',
        t('The email %email already has an alias in the list.',
        array('%email' => $email))
      );
      return;
    }
  }
  /**
   * (@inheritdoc)
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    db_insert('postfix_alias')
      ->fields(array(
        'source' => $form_state->getValue('email'),
        'destination' => $form_state->getValue('alias'),
      ))->execute();
    drupal_set_message(t('alias added'));
  }
}

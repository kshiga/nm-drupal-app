<?php

namespace Drupal\view_reps\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class LookupForm extends FormBase {

  public function getFormId() {
     return 'mymodule_settings';
   }

   public function buildForm(array $form, FormStateInterface $form_state) {
    $form['zip_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Example zip code'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    /*
    *  Retrieve
    *     create an object to hold an array of names that are associated to a representative set
    *     Retrieve should only occur if the zip code does not exist in the cache
    */

    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, $this->t('http://represent.opennorth.ca/postcodes/@postcode/?limit=1000', ['@postcode' => $form_state->getValue('zip_code')]));
    // eventually
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    $output=curl_exec($ch);
    curl_close($ch);

    /*
    *  Process the results
    *     create an object to hold an array of names that are associated to a representative set
    *     results processing should only occur if the zip code does not exist in the cache
    */

    $json = json_decode($output);
    $organized = (object) array();
    foreach ($json->{'representatives_centroid'} as $value) {
      $rep_set = str_replace(' ', '_', $value->{'representative_set_name'});
      $name = $value->{'name'};
      if(empty($organized->{$rep_set})){
        $organized->{$rep_set} = [$name];
      } else {
        array_push($organized->{$rep_set}, $name);
      }
    }

    /*
    * Add records to Cache
    *    records can be cached by zip code input
    *    adding the records to the cache should only occur if the zip code does not exist in the cache
    */

    /*
    * Render updated table
    *   KSH: An outstanding blocker, I am still unsure if it is possible to use a Form
    *   to  render objects outside of a form.  I have unsuccessfully attempted to utilize
    *   a template.  A method for programatically inserting HTML content into the document
    *   at this point has escaped my searches.
    *
    *  The commented code is part of the attempt to use a template, as is the definition
    *  of a template hook in view_reps.module
    */

    // $renderable = [
    //   '#theme' => 'view_reps',
    //   '#reps' => $organized,
    // ];
    // $rendered = \Drupal::service('renderer')->renderPlain($renderable);


    $this->messenger()->addStatus($this->t('Your representatives are @reps',['@reps' =>  json_encode($organized) ]));
  }


}

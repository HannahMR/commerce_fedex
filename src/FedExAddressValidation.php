<?php

namespace Drupal\commerce_fedex;

use NicholasCreativeMedia\FedExPHP\Services\AddressValidationService;
use NicholasCreativeMedia\FedExPHP\Structs\Address;
use NicholasCreativeMedia\FedExPHP\Structs\AddressToValidate;
use NicholasCreativeMedia\FedExPHP\Structs\AddressValidationRequest;
use NicholasCreativeMedia\FedExPHP\Structs\ClientDetail;
use NicholasCreativeMedia\FedExPHP\Structs\VersionId;
use NicholasCreativeMedia\FedExPHP\Structs\WebAuthenticationCredential;
use NicholasCreativeMedia\FedExPHP\Structs\WebAuthenticationDetail;
use Drupal\commerce_shipping\Entity\ShipmentInterface;


class FedExAddressValidation {

    static public function validateAddress(ShipmentInterface $shipment, $configuration){

    $address1 = $shipment->getShippingProfile()->address;

      /** @var \Drupal\Core\Field\FieldItemList $address_object */
    $address_object = $address1->first();

    $address_validation_service = new AddressValidationService();
    $address = new Address(
      $streetLines = [$address_object->getAddressLine1(), $address_object->getAddressLine2()],
      $city = $address_object->getLocality(),
      $stateOrProvinceCode = $address_object->getAdministrativeArea(),
      $postalCode = $address_object->getPostalCode(),
      $urbanizationCode = null,
      $countryCode = $address_object->getCountryCode(),
      $countryName = null,
      $residential = TRUE
    );
    $address_to_validate = new AddressToValidate(
      $clientReferenceId = null,
      $contact = null,
      $address
    );
    $address_array = array();
    $address_array[] = $address_to_validate;

    //$configuration['api_information']['api_password'];

    $creds = new WebAuthenticationCredential(
      $configuration['api_information']['api_key'],
      $configuration['api_information']['api_password']
    );

    $auth_info = new WebAuthenticationDetail(
      $creds,
      NULL
    );
    $client_details = new ClientDetail(
      $configuration['api_information']['account_number'],
      $configuration['api_information']['meter_number'],
      null,
      $localization = null
    );
    $version_id = new VersionId(
      $serviceId = $address_validation_service->version->ServiceId,
      $major = $address_validation_service->version->Major,
      $intermediate = $address_validation_service->version->Intermediate,
      $minor = $address_validation_service->version->Minor
    );
    $address_validation_request = new AddressValidationRequest(
      $auth_info,
      $client_details,
      $version_id,
      $transactionDetail = null,
      $inEffectAsOfTimestamp = null,
      $address_array
    );
    $valid_address = $address_validation_service->addressValidation($address_validation_request);
    $result = $valid_address ? $valid_address->getAddressResults()[0]->Classification : '';

    return $result;

  }

}

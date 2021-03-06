<?php
/**
 * The Uri Provider configuration contains the service to be used
 * to generate unique URI for newly created resources
 * 
 * Providers must implement common_uri_UriProvider
 * 
 * Default uri provider is core_kernel_uri_DatabaseSerialUriProvider
 * 
 * Alternatives:
 *     core_kernel_uri_AdvKeyValueUriProvider
 *     common_uri_MicrotimeUriProvider
 *     common_uri_MicrotimeRandUriProvider
 * 
 * @see core_kernel_uri_UriService
 */
return new core_kernel_uri_DatabaseSerialUriProvider(array('persistence' => 'default','namespace' => 'http://localhost/TAO_3.0.0_build/tao.rdf#'));

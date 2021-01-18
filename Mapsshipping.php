<?php

namespace Compania\Mapsshipping\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Config;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Http\Context as AuthContext;
/**
 * @category   Compania
 * @package    Compania_Mapsshipping
 * @author     Compania@gmail.com
 * @website    http://www.Compania.com
 */
class Mapsshipping extends AbstractCarrier implements CarrierInterface
{
    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = 'mpmapsshipping';

    /**
     * Whether this carrier has fixed rates calculation
     *
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    protected $_checkoutSession;
    private $customerSession;
    private $authContext;

    protected $_storeInfo;
    protected $_storeManagerInterface;
    

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        /* ScopeConfigInterface $scopeConfig, */
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        array $data = [],
        \Magento\Checkout\Model\Session $_checkoutSession,
        Context $context,
        Session $session,
        AuthContext $authContext,
        \Magento\Store\Model\Information $storeInfo,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_checkoutSession = $_checkoutSession;
        $this->customerSession = $session;
        $this->authContext = $authContext;
        $this->_storeInfo = $storeInfo;
        $this->_storeManagerInterface = $storeManagerInterface;
        $this->_storeManager = $storeManager;  
        $this->scopeConfig = $scopeConfig;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data, $context);

    }

    /**
     *
     *
     * @return array
     * @api
     */
    public function getAllowedMethods()
    {
        return [$this->getCarrierCode() => __($this->getConfigData('name'))];
    }
    

    /**
     * Collect and get rates for storefront
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param RateRequest $request
     * @return DataObject|bool|null
     * @api
     */
    public function collectRates(RateRequest $request)
    {
        /**
         * Make sure that Shipping method is enabled
         */
        if (!$this->isActive()) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $result = $this->_rateResultFactory->create();

        $shippingPrice = $this->getConfigData('price');

        

        include 'maps.php';

        $storeStreet =$this->_scopeConfig->getValue(
            'general/store_information/street_line1',
             \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        
        
        
        $storeCity =$this->_scopeConfig->getValue(
            'general/store_information/city',
             \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        
        $storeAddress = $storeStreet.', '.$storeCity;

        //Direccion establecida en el paso anterior, desde checkout

        $streetCheckout = $this->_checkoutSession->getQuote()->getShippingAddress()->getStreet();
                    $cityCheckout = $this->_checkoutSession->getQuote()->getShippingAddress()->getCity();
                    $postCodeCheckout = $this->_checkoutSession->getQuote()->getShippingAddress()->getPostCode();
                    $countryCheckout = $this->_checkoutSession->getQuote()->getShippingAddress()->getCountry();
                    // $id = $this->_checkoutSession->getQuote()->getCustomerId(); Linea para conseguir el id de el comprador 
    
                    $street2Checkout=implode(' ',$streetCheckout);
                    $addressCheckout = $street2Checkout.', '.$cityCheckout; //Esta variable almacena la direccion ingresada en el paso anterior, calle, codigo postal, ciudad, pais.
    
        //Evaluaremos si el usuario esta logeado
            
            if($this->customerSession->isLoggedIn()) {     

                
                                
                //Direccion establecida como direccion de envios por defecto

                    $customerId = $this->_checkoutSession->getQuote()->getCustomerId();
                    $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
                    $shippingAddress = $customer->getDefaultShippingAddress(); 
                    
                    $streetDefaultShipping=$shippingAddress->getStreet();
                    $cityDefaultShipping=$shippingAddress->getCity();
                    $postCodeDefaultShipping=$shippingAddress->getPostCode();
                    $countryIdDefaultShipping=$shippingAddress->getCountry();
                    $streetDefaultShipping=implode(' ',$streetDefaultShipping);
                    $addressDefaultShipping=$streetDefaultShipping.', '.$cityDefaultShipping;
    
                    
                //Direccion establecida como direccion de facturacion por defecto

                    $billingAddress = $customer->getDefaultBillingAddress();
                    $streetDefaultBilling=$billingAddress->getStreet();
                    $cityDefaultBilling=$billingAddress->getCity();
                    $postCodeDefaultBilling=$billingAddress->getPostCode();
                    $countryIdDefaultBilling=$billingAddress->getCountry();
                    $streetDefaultBilling=implode(' ',$streetDefaultBilling);
                    $addressDefaultBilling=$streetDefaultBilling.', '.$cityDefaultBilling;

                /**
                 * Ingresamos los metodos de envio, su titulo, y precio
                 */
                  

                    $methods = array(
                        0 => array(
                            'id' => '1',
                            'title' => 'Destino: '.$addressCheckout,
                            'method' => 'Direccion de envio ingresada ',
                            'price' => $precio=precio($storeStreet,$street2Checkout,$storeCity)
                        ),
                        1 => array(
                            'id' => '2',
                            'title' => 'Destino: '.$addressDefaultShipping,
                            'method' => 'Envio por defecto',
                            'price' => $precio=precio($storeStreet,$streetDefaultShipping,$storeCity)
                        ),
                        2 => array(
                            'id' => '3',
                            'title' => 'Destino: '.$addressDefaultBilling,
                            'method' => 'Envio a direccion de facturacion',
                            'price' => $precio=precio($storeStreet,$streetDefaultBilling,$storeCity)
                        ),
                    );
                    

                    
                    foreach($methods as $row){

                        if($row['price']!=0 ){
                        $method = $this->_rateMethodFactory->create();

                        $method->setCarrier($this->getCarrierCode());
                        /* $method->setCarrierTitle($this->getConfigData('title'));  */                    
                        $method->setCarrierTitle($row['title']);
                        $method->setMethod($row['id']);
                        /* $method->setMethodTitle($this->getConfigData('name')); */                    
                        $method->setMethodTitle($row['method']);
                        $method->setMethodApi1($this->getConfigData('api1'));
                        $method->setPrice($row['price']);
                        $method->setCost($shippingPrice);

                        $result->append($method);
                            }
                        }          
                    
                                       
                    return $result; 
            }else{
                // Esto ocurrira si el usuario no esta logeado

                    
                    $street = $this->_checkoutSession->getQuote()->getShippingAddress()->getStreet();
                    $city = $this->_checkoutSession->getQuote()->getShippingAddress()->getCity();
                    $postCode = $this->_checkoutSession->getQuote()->getShippingAddress()->getPostCode();
                    $country = $this->_checkoutSession->getQuote()->getShippingAddress()->getCountry();
                    $id = $this->_checkoutSession->getQuote()->getCustomerId();

                    $street2=implode(' ',$street);
                    $direccion = $street2.', '.$city; //Esta variable tiene la direccion completa

                    $shippingPrice=precio($storeStreet,$street2Checkout,$storeCity); 

                    if($shippingPrice!=0 ){

                    $method = $this->_rateMethodFactory->create();

                    $method->setCarrier($this->getCarrierCode());
                    // $method->setCarrierTitle($this->getConfigData('title'));  Esta es la linea original que setea el titulo del metodo de envio
                    $method->setCarrierTitle('Destino: '.$direccion);
                    $method->setCarrierApi1($this->getConfigData('api1')); 
                
                /**
                 * Displayed as shipping method under Carrier
                 */
                    $method->setMethod($this->getCarrierCode());
                    // $method->setMethodTitle($this->getConfigData('name'));  Esta es la linea original que setea el nombre del metodo de envio                
                    $method->setMethodTitle('Envio a direccion ingresada');
                    $method->setMethodApi1($this->getConfigData('api1'));
                    $method->setPrice( $shippingPrice);
                    $method->setCost( $shippingPrice);
        
                    $result->append($method);
                    
                    return $result;
                    }
                  }
            
         
                
        
    }

    
   
}
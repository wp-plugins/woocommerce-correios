<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WC_Correios_API class.
 */
class WC_Correios_API {

	/**
	 * Webservice URL.
	 *
	 * @var string
	 */
	private $_webservice = 'http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx?';

	/**
	 * Services ID.
	 *
	 * 41106 - PAC without contract.
	 * 40010 - SEDEX without contract.
	 * 40215 - SEDEX 10 without contract.
	 * 40290 - SEDEX Hoje without contract.
	 * 41068 - PAC with contract.
	 * 40096 - SEDEX with contract.
	 * 81019 - e-SEDEX with contract.
	 *
	 * @var array
	 */
	protected $services = array();

	/**
	 * Origin zipcode.
	 *
	 * @var string
	 */
	protected $zip_origin = '';

	/**
	 * Destination zipcode.
	 *
	 * @var string
	 */
	protected $zip_destination = '';

	/**
	 * Package height.
	 *
	 * @var float
	 */
	protected $height = 0;

	/**
	 * Package width.
	 *
	 * @var float
	 */
	protected $width = 0;

	/**
	 * Package diameter.
	 *
	 * @var float
	 */
	protected $diameter = 0;

	/**
	 * Package length.
	 *
	 * @var float
	 */
	protected $length = 0;

	/**
	 * Package weight.
	 *
	 * @var float
	 */
	protected $weight = 0;

	/**
	 * Correios username.
	 *
	 * @var string
	 */
	protected $login = '';

	/**
	 * Correios password.
	 *
	 * @var string
	 */
	protected $password = '';

	/**
	 * Declared value.
	 *
	 * @var string
	 */
	protected $declared_value = '0';

	/**
	 * Package format.
	 *
	 * 1 – box/package
	 * 2 – roll/prism
	 * 3 - envelope
	 *
	 * @var string
	 */
	protected $format = '1';

	/**
	 * Own hand service.
	 *
	 * @var string
	 */
	protected $own_hand = 'N';

	/**
	 * Receipt notice service.
	 *
	 * @var string
	 */
	protected $receipt_notice = 'N';

	/**
	 * Initialize the API class.
	 *
	 * @param string    $debug Debug mode.
	 * @param WC_Logger $log   Logger class.
	 */
	public function __construct( $debug, $log ) {
		$this->id    = WC_Correios::get_method_id();
		$this->debug = $debug;
		$this->log   = $log;
	}

	/**
	 * Set the services.
	 *
	 * @param array $services
	 */
	public function set_services( $services = array() ) {
		$this->services = $services;
	}

	/**
	 * Set the origin zipcode.
	 *
	 * @param string $zip_origin
	 */
	public function set_zip_origin( $zip_origin = '' ) {
		$this->zip_origin = $zip_origin;
	}

	/**
	 * Set the destination zipcode.
	 *
	 * @param string $zip_destination
	 */
	public function set_zip_destination( $zip_destination = '' ) {
		$this->zip_destination = $zip_destination;
	}

	/**
	 * Set the package height.
	 *
	 * @param float $height
	 */
	public function set_height( $height = 0 ) {
		$this->height = $height;
	}

	/**
	 * Set the package width.
	 *
	 * @param float $width
	 */
	public function set_width( $width = 0 ) {
		$this->width = $width;
	}

	/**
	 * Set the package diameter.
	 *
	 * @param float $diameter
	 */
	public function set_diameter( $diameter = 0 ) {
		$this->diameter = $diameter;
	}

	/**
	 * Set the package length.
	 *
	 * @param float $length
	 */
	public function set_length( $length = 0 ) {
		$this->length = $length;
	}

	/**
	 * Set the package weight.
	 *
	 * @param float $weight
	 */
	public function set_weight( $weight = 0 ) {
		$this->weight = $weight;
	}

	/**
	 * Set the Correios username.
	 *
	 * @param string $login
	 */
	public function set_login( $login = '' ) {
		$this->login = $login;
	}

	/**
	 * Set the Correios password.
	 *
	 * @param string $password
	 */
	public function set_password( $password = '' ) {
		$this->password = $password;
	}

	/**
	 * Set the declared value.
	 *
	 * @param string $declared_value
	 */
	public function set_declared_value( $declared_value = '0' ) {
		$this->declared_value = $declared_value;
	}

	/**
	 * Set the package format.
	 *
	 * @param string $format
	 */
	public function set_format( $format = '1' ) {
		$this->format = $format;
	}

	/**
	 * Set the Own hand option.
	 *
	 * @param string $own_hand
	 */
	public function set_own_hand( $own_hand = 'N' ) {
		$this->own_hand = $own_hand;
	}

	/**
	 * Set the receipt notice.
	 *
	 * @param string $receipt_notice
	 */
	public function set_receipt_notice( $receipt_notice = 'N' ) {
		$this->receipt_notice = $receipt_notice;
	}

	/**
	 * Fix number format for SimpleXML.
	 *
	 * @param  float $value  Value with dot.
	 *
	 * @return string        Value with comma.
	 */
	protected function float_to_string( $value ) {
		$value = str_replace( '.', ',', $value );

		return $value;
	}

	/**
	 * Clean Zipcode.
	 *
	 * @param  string $zip Zipcode.
	 *
	 * @return string      Cleaned zipcode.
	 */
	protected function clean_zipcode( $zip ) {
		$fixed = preg_replace( '([^0-9])', '', $zip );

		return $fixed;
	}

	/**
	 * Gets the service name.
	 *
	 * @param  int   $service Correios service ID.
	 *
	 * @return array          Correios service name.
	 */
	public static function get_service_name( $service ) {
		$name = array(
			'41106' => 'PAC',
			'40010' => 'SEDEX',
			'40215' => 'SEDEX 10',
			'40290' => 'SEDEX Hoje',
			'41068' => 'PAC',
			'40096' => 'SEDEX',
			'81019' => 'e-SEDEX',
		);

		if ( ! isset( $name[ $service ] ) ) {
			return '';
		}

		return $name[ $service ];
	}

	/**
	 * Get shipping prices.
	 *
	 * @return array
	 */
	public function get_shipping() {
		$values = array();

		// Checks if services and zipcode is empty.
		if (
			! is_array( $this->services )
			|| empty( $this->services )
			|| empty( $this->zip_destination )
			|| empty( $this->zip_origin )
		) {
			return $values;
		}

		foreach ( $this->services as $service ) {

			$args = apply_filters( 'woocommerce_correios_shipping_args', array(
				'nCdServico'          => $service,
				'nCdEmpresa'          => $this->login,
				'sDsSenha'            => $this->password,
				'sCepDestino'         => $this->clean_zipcode( $this->zip_destination ),
				'sCepOrigem'          => $this->clean_zipcode( $this->zip_origin ),
				'nVlAltura'           => $this->float_to_string( $this->height ),
				'nVlLargura'          => $this->float_to_string( $this->width ),
				'nVlDiametro'         => $this->float_to_string( $this->diameter ),
				'nVlComprimento'      => $this->float_to_string( $this->length ),
				'nVlPeso'             => $this->float_to_string( $this->weight ),
				'nCdFormato'          => $this->format,
				'sCdMaoPropria'       => $this->own_hand,
				'nVlValorDeclarado'   => $this->declared_value,
				'sCdAvisoRecebimento' => $this->receipt_notice,
				'StrRetorno'          => 'xml'
			) );

			$url = add_query_arg( $args, $this->_webservice );

			if ( 'yes' == $this->debug ) {
				$this->log->add( $this->id, 'Requesting the Correios WebServices...' );
			}

			// Gets the WebServices response.
			$response = wp_remote_get( $url, array( 'sslverify' => false, 'timeout' => 30 ) );

			if ( is_wp_error( $response ) ) {
				if ( 'yes' == $this->debug ) {
					$this->log->add( $this->id, 'WP_Error: ' . $response->get_error_message() );
				}
			} elseif ( $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
				$result = new SimpleXmlElement( $response['body'], LIBXML_NOCDATA );

				if ( 'yes' == $this->debug ) {
					$this->log->add( $this->id, 'Correios WebServices response [' . self::get_service_name( $service ) . ']: ' . print_r( $result->cServico, true ) );
				}

				$values[ $service ] = $result->cServico;
			} else {
				if ( 'yes' == $this->debug ) {
					$this->log->add( $this->id, 'Error accessing the Correios WebServices [' . self::get_service_name( $service ) . ']: ' . $response['response']['code'] . ' - ' . $response['response']['message'] );
				}
			}
		}

		return $values;
	}
}

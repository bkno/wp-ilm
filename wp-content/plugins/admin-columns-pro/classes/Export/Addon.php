<?php

namespace ACP\Export;

use AC\Asset\Location;
use AC\ListScreenRepository;
use AC\Registerable;
use ACP;
use ACP\RequestAjaxHandlers;
use ACP\RequestAjaxParser;

class Addon implements Registerable {

	/**
	 * @var Location
	 */
	private $location;

	/**
	 * @var ListScreenRepository
	 */
	private $list_screen_repository;

	public function __construct(
		Location $location,
		ListScreenRepository $list_screen_repository
	) {
		$this->location = $location;
		$this->list_screen_repository = $list_screen_repository;
	}

	public function register() {
		$request_ajax_handlers = new RequestAjaxHandlers();
		$request_ajax_handlers->add( 'acp-export-file-name', new RequestHandler\Ajax\FileName( $this->list_screen_repository ) )
		                      ->add( 'acp-export-order-preference', new RequestHandler\Ajax\SaveExportPreference() )
		                      ->add( 'acp-export-show-export-button', new RequestHandler\Ajax\ToggleExportButtonTable() );

		$services = [
			new Admin(),
			new RequestAjaxParser( $request_ajax_handlers ),
			new Settings( $this->location ),
			new TableScreen( $this->location ),
		];

		foreach ( $services as $service ) {
			if ( $service instanceof Registerable ) {
				$service->register();
			}
		}
	}

}
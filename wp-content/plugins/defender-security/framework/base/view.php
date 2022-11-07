<?php
/**
 * Author: Hoang Ngo
 */

namespace Calotes\Base;

class View extends Component {
	const EVENT_BEFORE_RENDER = 'beforeRender', EVENT_AFTER_RENDER = 'afterRender';

	/**
	 * @var array
	 */
	public $blocks = [];
	/**
	 * @var array
	 */
	public $params = [];
	/**
	 * The template file in which this view should be rendered.
	 *
	 * @var null
	 */
	public $layout = null;
	/**
	 * The file contains content of this view, relative path.
	 *
	 * @var null
	 */
	public $view_file = null;

	/**
	 * @var array
	 */
	protected $_cache_stack = [];
	/**
	 * The folder contains view files, absolute path.
	 *
	 * @var null
	 */
	private $_base_path = null;

	public function __construct( $base_path ) {
		$this->_base_path = $base_path;
	}

	/**
	 * Render a view file. This will be used to render a whole page.
	 * If a layout is defined, then we will render layout + view.
	 *
	 * @param $view
	 * @param array $params
	 *
	 * @return string
	 */
	public function render( $view, $params = [] ) {
		$this->trigger( self::EVENT_BEFORE_RENDER );
		$view_file = $this->_base_path . DIRECTORY_SEPARATOR . $view . '.php';
		if ( is_file( $view_file ) ) {
			$content = $this->render_php_file( $view_file, $params );
			$this->trigger( self::EVENT_AFTER_RENDER );

			return $content;
		}

		return '';
	}

	/**
	 * @param $file
	 * @param array $params
	 *
	 * @return string
	 */
	private function render_php_file( $file, $params = [] ) {
		ob_start();
		ob_implicit_flush( false );
		extract( $params, EXTR_OVERWRITE );
		require $file;

		return ob_get_clean();
	}

	/**
	 * Todo: need?
	 * Starting a cache block.
	 */
	public function begin_cache( $id ) {
		ob_start();
		$this->_cache_stack[ $id ] = null;
	}

	/**
	 * Todo: need?
	 * @param $id
	 *
	 * @return string
	 */
	public function end_cache( $id ) {
		$cached = ob_get_clean();
		$this->_cache_stack[ $id ] = $cached;

		return $cached;
	}

	/**
	 * Todo: need?
	 * Get cache content cached.
	 *
	 * @param $id
	 *
	 * @return mixed|null
	 */
	public function get_cache( $id ) {
		return isset( $this->_cache_stack[ $id ] ) ?? null;
	}
}

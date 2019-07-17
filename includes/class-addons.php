<?php

defined( 'ABSPATH' ) || exit;

class GMA_Addons {

    private $endpoint = 'http://www.siaeb.com/wp-json/yashita/v1/addons/get';

	public function __construct() {
	    add_filter( 'gma_backend_pages', function( $pages ) {
	        $pages[] = 'gma-addons';
	        return $pages;
        } );
		add_action( 'admin_menu', [$this, 'admin_menu'] );
	}


	public function admin_menu() {
		add_submenu_page( 'give-me-answer-lite',
			__( 'Add-Ons','give-me-answer-lite' ),
			sprintf( '<span style="color:darkorange;">%s</span>', __( 'Add-Ons','give-me-answer-lite' ) ),
			'manage_options',
			'gma-addons',
			array( $this, 'display' )
		);
	}

	public function display() {
		?>
            <style>
                .btn-info {
                    color: #fff !important;
                    background-color: #45aaf2 !important;
                    border-color: #45aaf2 !important;
                }

                .rounded-pill {
                    border-radius: 50rem !important;
                }

                .yha-addon-no-text-decoration {
                    text-decoration: none !important;
                }

                .siaeb-plugin__thumbnail {
                    width: 128px !important;
                    height: 128px !important;
                }

                .siaeb-plugin__name{
                    font-size: 1.1rem !important;
                    color: #0073aa !important;
                }

                .siaeb-plugin__footer {
                    clear: both;
                    padding: 12px 20px;
                    background-color: #fafafa;
                    border-top: 1px solid #ddd;
                    overflow: hidden;
                }

                .siaeb-plugin .cover-ribbon {
                    height: 115px;
                    width: 115px;
                    position: absolute;
                    top: -7px;
                    overflow: hidden;
                }

                .siaeb-plugin .cover-ribbon .cover-ribbon-inside {
                    background: #EA4335;
                    color: #FFF;
                    transform: rotate(-45deg);
                    position: absolute;
                    left: -35px;
                    top: 16px;
                    padding: 10px;
                    min-width: 127px;
                    text-align: center;
                    z-index: 9;
                }

                .siaeb-plugin .cover-ribbon .cover-ribbon-inside:before {
                    width: 0;
                    height: 0;
                    border-left: 7px solid transparent;
                    border-right: 7px solid transparent;
                    border-bottom: 10px solid #992C23;
                    position: absolute;
                    left: -5px;
                    bottom: 0;
                    content: "";
                    top: 34px;
                    transform: rotate(-45deg);
                }

                .siaeb-plugin .cover-ribbon .cover-ribbon-inside:after {
                    width: 0;
                    height: 0;
                    border-top: 7px solid transparent;
                    border-left: 7px solid #992C23;
                    border-bottom: 7px solid transparent;
                    position: absolute;
                    top: 33px;
                    bottom: 0;
                    right: -1px;
                    content: "";
                    transform: rotate(-45deg);
                }

            </style>
			<div class="gma">

                    <?php gma_lite()->utility->show_page_title(__('Extensions for "Give Me Answer"', 'give-me-answer-lite')); ?>

					<div class="row mx-0">

						<div class="col-12 px-1 mb-4">
							<div class="alert alert-info font-weight-bold text-secondary my-1">
								<?php _e('Add-Ons add more functionality to "Give Me Answer" plugin.', 'give-me-answer-lite'); ?>
							</div>
						</div>

                        <?php
                            $args     = array(
                                'timeout' => 15,
                                'body'    => [
                                    'token' => defined( 'SIAEB_PRODUCT_TOKEN' ) ? SIAEB_PRODUCT_TOKEN : '',
                                ],
                                'headers' => array(
                                    'accept' => 'application/json',
                                )
                            );
                            $response = wp_remote_retrieve_body( wp_remote_get( $this->endpoint, $args ) );
                            if ( is_wp_error( $response ) ) {
                                ?>
                                    <div class="alert alert-warning border-warning text-danger text-center mt-5">
                                        <?php _e('Error communicating with server...', 'give-me-answer-lite'); ?>
                                    </div>
                                <?php
                            } else {
                                $response = json_decode( $response );
	                            if ( isset( $response->found ) && $response->found ) {
	                                foreach ( $response->plugins as $plugin ) {
		                                ?>

                                        <div class="col-md-6 col-lg-6 col-xl-4 px-0 siaeb-plugin mb-1 pt-1">
                                            <?php if ( $plugin->isnew == 'yes' ) { ?>
                                                <div class="cover-ribbon">
                                                    <div class="cover-ribbon-inside"><?php _e('New!', 'give-me-answer-lite'); ?></div>
                                                </div>
                                            <?php } ?>
                                            <div class="bg-white mx-1 border">
                                                <div class="d-flex siaeb-plugin__header">
                                                    <div>
                                                        <a href="<?php echo $plugin->url; ?>" target="_blank">
                                                            <img class="siaeb-plugin__thumbnail" src="<?php echo $plugin->thumbnail; ?>" width="284" height="180" alt="<?php echo $plugin->name; ?>">
                                                        </a>
                                                    </div>
                                                    <div class="p-3">
                                                        <h4><a class="yha-addon-no-text-decoration siaeb-plugin__name mb-2" target="_blank" href="<?php echo $plugin->url; ?>"><?php echo esc_html($plugin->name); ?></a></h4>
                                                        <p><?php echo esc_html($plugin->description); ?></p>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between siaeb-plugin__footer">
                                                    <div class="d-flex flex-column">
                                                        <span class="siaeb-plugin__footer__version mb-1">
                                                            <?php echo sprintf('<span class="font-weight-bold">%s</span> : %s', __('Version', 'yashita'), $plugin->version); ?>
                                                        </span>
                                                        <span class="siaeb-plugin__footer__status">
                                                            <?php echo sprintf('<span class="font-weight-bold">%s</span> :', __('Status', 'yashita')); ?>
                                                            <?php if ( ! is_plugin_active($plugin->slug) && ! is_plugin_inactive($plugin->slug) ) { ?>
                                                                <a href="<?php echo $plugin->url; ?>" target="_blank" class="btn btn-info btn-lg yha-addon__more rounded-pill">
                                                                <?php _e('Not Installed', 'yashita'); ?>
                                                            </a>
                                                            <?php } else { ?>
                                                               <span class="text-success font-weight-bold ml-1"><?php _e('Installed', 'yashita'); ?></span>
                                                            <?php } ?>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <?php if ( ! is_plugin_active($plugin->slug) && ! is_plugin_inactive($plugin->slug)  ) {  ?>
                                                        <span class="siaeb-plugin__footer__price text-success font-weight-bold"><?php echo $plugin->price; ?></span>
                                                        <a class="btn btn-info btn-sm text-white px-3" target="_blank" href="<?php echo $plugin->url; ?>"><?php _e('Buy Add-On', 'yashita'); ?></a>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

		                                <?php
	                                }
	                            }
	                        }
	                    ?>

					</div>
			</div>
		<?php
	}

}
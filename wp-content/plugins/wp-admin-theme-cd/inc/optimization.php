<?php 

/*****************************************************************/
/* WP OPTIMIZATION TIPPS ADMIN PAGE */
/*****************************************************************/

if ( ! function_exists( 'wp_admin_theme_cd_wp_optimize_tipps_admin_menu' ) ) :

	function wp_admin_theme_cd_wp_optimize_tipps_admin_menu() {
			
		add_submenu_page(
			'tools.php',
			esc_html__( 'WP Admin Theme CD - Optimization Tipps', 'wp-admin-theme-cd' ),
			esc_html__( 'WPAT Optimization', 'wp-admin-theme-cd' ),
			'manage_options',
			'wp-admin-theme-cd-optimize_tipps',
			'wp_admin_theme_cd_optimize_tipps_page'
		);
		
	}

	add_action( 'admin_menu', 'wp_admin_theme_cd_wp_optimize_tipps_admin_menu' );

	function wp_admin_theme_cd_optimize_tipps_page() { 
        global $wpdb; 
        $common = new WPAT_Sys_info(); 

        $help = '<span class="dashicons dashicons-editor-help"></span>';
        $solved = '<span class="sys-status enable"><span class="dashicons dashicons-yes"></span> ' . esc_html__( 'Solved', 'wp-admin-theme-cd' ) . '</span>';
        $unsolved = '<span class="sys-status disable"><span class="dashicons dashicons-no"></span> ' . esc_html__( 'Unsolved', 'wp-admin-theme-cd' ) . '</span>';
        $yes = '<span class="sys-status enable"><span class="dashicons dashicons-yes"></span> ' . esc_html__( 'Yes', 'wp-admin-theme-cd' ) . '</span>';
        $no = '<span class="sys-status disable"><span class="dashicons dashicons-no"></span> ' . esc_html__( 'No', 'wp-admin-theme-cd' ) . '</span>';
        $entered = '<span class="sys-status enable"><span class="dashicons dashicons-yes"></span> ' . esc_html__( 'Defined', 'wp-admin-theme-cd' ) . '</span>';
        $not_entered = '<span class="sys-status disable"><span class="dashicons dashicons-no"></span> ' . esc_html__( 'Not defined', 'wp-admin-theme-cd' ) . '</span>';
        $sec_key = '<span class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html__( 'Please enter this security key in the wp-confiq.php file', 'wp-admin-theme-cd' ) . '!</span>'; ?>
	
        <div class="wrap">
            <h1><?php echo esc_html__( 'WP Admin Theme CD - WP Optimization Tipps', 'wp-admin-theme-cd' ); ?></h1>

            <h2><?php echo esc_html__( 'Speed Up your WordPress Admin Area', 'wp-admin-theme-cd' ); ?></h2>
            
            <p><?php echo __( 'Follow the following optimization tipps, to get your WordPress admin area faster than before. Sometimes the page load time is very ... while we working on our website...', 'wp-admin-theme-cd' ); ?>.</p>
            
            <table class="wp-list-table widefat fixed striped posts">
                <thead>
                    <tr>
                        <th width="20%" class="manage-column"><?php esc_html_e( 'Info', 'wp-admin-theme-cd' ); ?></th>
                        <th width="10%" class="manage-column"><?php esc_html_e( 'Status', 'wp-admin-theme-cd' ); ?></th>
                        <th class="manage-column"><?php esc_html_e( 'Tipp', 'wp-admin-theme-cd' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php esc_html_e( 'WP Version', 'wp-admin-theme-cd' ); ?>:</td>
                        <td>
                            <?php if( $test ) {
                                echo $unsolved;
                            } else {
                                echo $solved;
                            } ?>
                        </td>
                        <td><strong><?php bloginfo('version'); ?></strong></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('PHP Version', 'wp-admin-theme-cd'); ?>:</td>
                        <td>
                            <?php if( $test ) {
                                echo $unsolved;
                            } else {
                                echo $solved;
                            } ?>
                        </td>
                        <td><?php echo $common->getPhpVersion(); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e( 'MySQL Version', 'wp-admin-theme-cd' ); ?>:</td>
                        <td>
                            <?php if( $test ) {
                                echo $unsolved;
                            } else {
                                echo $solved;
                            } ?>
                        </td>
                        <td><?php echo $common->getMySQLVersion(); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e( 'PHP Memory WP-Limit', 'wp-admin-theme-cd' ); ?>:</td>
                        <td>
                            <?php if( $test ) {
                                echo $unsolved;
                            } else {
                                echo $solved;
                            } ?>
                        </td>
                        <td><?php
                            $memory = $common->memory_size_convert( WP_MEMORY_LIMIT );

                            if ($memory < 67108864) {
                                echo '<span class="warning"><span class="dashicons dashicons-warning"></span> ' . sprintf(__('%s - For better performance, we recommend setting memory to at least 64MB. See: %s', 'wp-admin-theme-cd'), size_format($memory), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">' . __('Increasing memory allocated to PHP', 'wp-admin-theme-cd') . '</a>') . '</span>';
                            } else {
                                echo '<strong>' . size_format($memory) . '</strong>';
                            }
                            ?> 
                        </td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e( 'PHP Memory Server-Limit', 'wp-admin-theme-cd' ); ?>:</td>
                        <td>
                            <?php if( $test ) {
                                echo $unsolved;
                            } else {
                                echo $solved;
                            } ?>
                        </td>
                        <td>
                            <?php
                            if (function_exists('memory_get_usage')) {
                                $system_memory = $common->memory_size_convert(@ini_get('memory_limit'));
                                $memory = max($memory, $system_memory);
                            }

                            if ($memory < 67108864) {
                                echo '<span class="warning"><span class="dashicons dashicons-warning"></span> ' . sprintf(__('%s - For better performance, we recommend setting memory to at least 64MB. See: %s', 'wp-admin-theme-cd'), size_format($memory), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">' . __('Increasing memory allocated to PHP', 'wp-admin-theme-cd') . '</a>') . '</span>';
                            } else {
                                echo '<strong>' . size_format($memory) . '</strong>';
                            }
                            ?> 
                        </td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e( 'PHP Memory WP-Usage', 'wp-admin-theme-cd' ); ?>:</td>
                        <td>
                            <?php if( $test ) {
                                echo $unsolved;
                            } else {
                                echo $solved;
                            } ?>
                        </td>
                        <td>
                            <?php if( $common->wp_memory_usage() == '-1' ) {
                                echo $common->wp_memory_usage()['MemUsed'] . ' MB of -1 / ' . esc_html__( 'Unlimited', 'wp-admin-theme-cd' );
                            } else { ?>
                                <div class="status-progressbar"><span><?php echo $common->wp_memory_usage()['MemUsage'] . '% '; ?></span><div style="width: <?php echo $common->wp_memory_usage()['MemUsage']; ?>%"></div></div>
                                <?php echo ' ' . $common->wp_memory_usage()['MemUsed'] . ' MB of ' . (int)WP_MEMORY_LIMIT . ' MB'; ?>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Post Revisions</td>
                        <td>
                            <?php if( $test ) {
                                echo $unsolved;
                            } else {
                                echo $solved;
                            } ?>
                        </td>
                        <td>slow</td>
                    </tr>
                    <tr>
                        <td>Spam / Trash Comments</td>
                        <td>
                            <?php if( $test ) {
                                echo $unsolved;
                            } else {
                                echo $solved;
                            } ?>
                        </td>
                        <td>slow</td>
                    </tr>
                    <tr>
                        <td>Debug Modes</td>
                        <td>
                            <?php if( $test ) {
                                echo $unsolved;
                            } else {
                                echo $solved;
                            } ?>
                        </td>
                        <td>WordPress Debug Mode is activate. This is slow up your backend. If you don't need it, while developing on your website, you can disable WordPress Debug Mode in the wp-confiq.php file ...</td>
                    </tr>
                    <tr>
                        <td>Admin Speed (Compress)</td>
                        <td>
                            <?php if( $test ) {
                                echo $unsolved;
                            } else {
                                echo $solved;
                            } ?>
                        </td>
                        <td>slow</td>
                    </tr>
                    <tr>
                        <td>Emoji</td>
                        <td>
                            <?php if( $test ) {
                                echo $unsolved;
                            } else {
                                echo $solved;
                            } ?>
                        </td>
                        <td>WordPress is loading the Emoji scripts. This is slow up your backend. If you don't need it, you can disable WordPress Emojis at ...</td>
                    </tr>
                </tbody>
            </table>
            
            <br><br>
            
        </div>

    <?php }

endif;
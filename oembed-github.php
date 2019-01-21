<?php

/*
Plugin Name: oEmbed GitHub
Plugin URI: https://github.com/nathan-fiscaletti/oembed-github
Description: Description: oEmbed service for GitHub repositories, commits, pull-requests, profiles, issues, and Gists
Version: 1.2
Author: Nathan Fiscaletti
Author URI: https://www.nathanf.tk/
*/

class oEmbedGitHub
{
    /**
     * ==========================================================================
     * Plugin: Main entry point for plugin
     * ==========================================================================
     */

    /**
     * Construct the main plugin file.
     */
    public function run()
    {
        add_action( 'wp_enqueue_scripts', array( $this, 'loadScripts' ) );

        add_filter( 'http_request_timeout', array( $this, 'oEmbedApiTimeout' ) );
        add_action('init', array( $this, 'oEmbedRegisterServices' ));
        add_action('init', array( $this, 'oEmbedValidateRequest' ));
        add_action('admin_menu', array( $this, 'adminMenu' ));
    }

    /**
     * Adds the menu item to the admin menu.
     */
    public function adminMenu()
    {
        add_menu_page( 'oEmbed GitHub Settings', 'oEmbed GitHub', 'manage_options', 'oembed-github-settings', [$this, 'adminPage'], 'dashicons-editor-code', 6  );
    }

    /**
     * Handles the page display for the admin
     * configuration page.
     */
    function adminPage()
    {
        ?>
                <script>
                    function oembed_github_update_background_color(id, color) {
                        document.getElementById(id).style.backgroundColor = color;
                    }
                </script>
                <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">

                <h1>oEmbed Github - Settings</h1>
                <hr />
                
                <br />
                <h2 id="github-api">GitHub API Configuration</h2>

                <!-- Handle the API form -->
                <?php
                    if (isset($_GET['submit_client_id'])) {
                        wp_cache_delete ('alloptions', 'options');
                        if (
                            ! empty($_POST['oembed-github-client-id']) && 
                            ! empty($_POST['oembed-github-client-secret'])
                        ) {
                            $result = update_option('oembed-github-client-id', $_POST['oembed-github-client-id'], true);
                            $result = update_option('oembed-github-client-secret', $_POST['oembed-github-client-secret'], true);
                            echo '<h3 style="color: green;">Updated GitHub API Configuration!</h3>';
                        }
                    }

                    $submit_url = basename($_SERVER['REQUEST_URI']);
                    $oembed_github_client_id = get_option('oembed-github-client-id');
                    $oembed_github_client_id = ($oembed_github_client_id === false) ? '' : $oembed_github_client_id;
                    $oembed_github_client_secret = get_option('oembed-github-client-secret');
                    $oembed_github_client_secret = ($oembed_github_client_secret === false) ? '' : $oembed_github_client_secret;
                ?>

                <form method="post" action="<?= $submit_url; ?>&submit_client_id#github-api">
                    <p>
                        Enter your oAuth Client ID and Client secret from your GitHub application configuration.<br />
                        This will greatly increase the API rate limit.<br />
                        <a href="https://github.com/settings/applications/new" target="_blank">More Info</a>
                    </p>
                    <table>
                        <tr>
                            <td>Client ID</td><td>:</td><td><input type="text" name="oembed-github-client-id" value="<?= $oembed_github_client_id ?>" style='width: 300px;' /></td>
                        </tr>
                        <tr>
                            <td>Client Secret</td><td>:</td><td><input type="text" name="oembed-github-client-secret" value="<?= $oembed_github_client_secret ?>" style='width: 300px;' /></td>
                        </tr>
                    </table>
                    <button type="submit" name="submit" id="submit_api" class="button button-primary"><i class="far fa-save"></i> &nbsp;Save Changes</button>
                </form>

                <br /><br />
                <h2 id="oembed-cache">oEmbed Cache</h2>

                <?php
                    if (isset($_GET['delete_cache'])) {
                        if (isset($_POST['oembed-github-clear-cache']) && $_POST['oembed-github-clear-cache'] == 1) {
                            global $wpdb, $table_prefix;
                            
                            $sql = "SELECT * FROM {$table_prefix}postmeta WHERE meta_key LIKE '_oembed_%'";
                            $results = $wpdb->get_results( $sql , 'ARRAY_A');

                            foreach ($results as $postMeta) {
                                delete_metadata( 'post', $postMeta['post_id'], $postMeta['meta_key']);
                            }

                            echo '<h3 style="color: green;">oEmbed cache cleared!</h3>';
                        }
                    }
                ?>

                <form method="post" action="<?= $submit_url; ?>&delete_cache#oembed-cache">
                    <p>
                        Clearing the oEmbed cache will cause all oEmbed information to be updated and any new oEmbed blocks to retrieve new information.<br />
                        <a href="https://siteorigin.com/clearing-oembed-cache/" target="_blank">More Info</a>
                    </p>
                    <input type="hidden" name="oembed-github-clear-cache" value="1" />
                    <button type="submit" name="submit" id="submit_delete_cache" class="button button-primary"><i class="far fa-trash-alt"></i> &nbsp;Delete Cache</button>
                </form>

                <br />
                <br />
                <h2 id='theme-configuration'>GitHub Theme Configuration</h2>

                <!-- Handle the theme form -->
                <?php
                    if (isset($_GET['submit_theme'])) {
                        wp_cache_delete ('alloptions', 'options');
                        if (
                            ! empty($_POST['oembed-github-background-color']) && 
                            ! empty($_POST['oembed-github-top-background-color']) &&
                            ! empty($_POST['oembed-github-subheader-background-color']) &&
                            ! empty($_POST['oembed-github-main-font-color']) &&
                            ! empty($_POST['oembed-github-sub-title-font-color']) &&
                            ! empty($_POST['oembed-github-sub-header-font-color']) &&
                            ! empty($_POST['oembed-github-description-font-color']) &&
                            ! empty($_POST['oembed-github-stat-header-font-color']) &&
                            ! empty($_POST['oembed-github-stat-sub-header-font-color']) &&
                            ! empty($_POST['oembed-github-open-color']) &&
                            ! empty($_POST['oembed-github-closed-color']) &&
                            ! empty($_POST['oembed-github-merged-color']) &&
                            ! empty($_POST['oembed-github-stats-secondary-color']) &&
                            ! empty($_POST['oembed-github-logo-color'])
                        ) {
                            $result = update_option('oembed-github-background-color', $_POST['oembed-github-background-color'], true);
                            $result = update_option('oembed-github-top-background-color', $_POST['oembed-github-top-background-color'], true);
                            $result = update_option('oembed-github-subheader-background-color', $_POST['oembed-github-subheader-background-color'], true);
                            $result = update_option('oembed-github-main-font-color', $_POST['oembed-github-main-font-color'], true);
                            $result = update_option('oembed-github-sub-title-font-color', $_POST['oembed-github-sub-title-font-color'], true);
                            $result = update_option('oembed-github-sub-header-font-color', $_POST['oembed-github-sub-header-font-color'], true);
                            $result = update_option('oembed-github-description-font-color', $_POST['oembed-github-description-font-color'], true);
                            $result = update_option('oembed-github-stat-header-font-color', $_POST['oembed-github-stat-header-font-color'], true);
                            $result = update_option('oembed-github-stat-sub-header-font-color', $_POST['oembed-github-stat-sub-header-font-color'], true);
                            $result = update_option('oembed-github-stats-secondary-color', $_POST['oembed-github-stats-secondary-color'], true);
                            $result = update_option('oembed-github-open-color', $_POST['oembed-github-open-color'], true);
                            $result = update_option('oembed-github-closed-color', $_POST['oembed-github-closed-color'], true);
                            $result = update_option('oembed-github-merged-color', $_POST['oembed-github-merged-color'], true);
                            $result = update_option('oembed-github-logo-color', $_POST['oembed-github-logo-color'], true);
                            echo '<h3 style="color: green;">Updated GitHub Theme Configuration!</h3>';
                        }
                    }

                    if (isset($_GET['reset_theme'])) {
                        if (isset($_POST['oembed-github-reset-theme']) && $_POST['oembed-github-reset-theme'] == 1) {
                            $result = update_option('oembed-github-background-color', '#5C7CFA', true);
                            $result = update_option('oembed-github-top-background-color', '#FFFFFF', true);
                            $result = update_option('oembed-github-subheader-background-color', '#EEEEEE', true);
                            $result = update_option('oembed-github-main-font-color', '#000000', true);
                            $result = update_option('oembed-github-sub-title-font-color', '#000000', true);
                            $result = update_option('oembed-github-sub-header-font-color', '#000000', true);
                            $result = update_option('oembed-github-description-font-color', '#000000', true);
                            $result = update_option('oembed-github-stat-header-font-color', '#000000', true);
                            $result = update_option('oembed-github-stat-sub-header-font-color', '#777777', true);
                            $result = update_option('oembed-github-open-color', '#2cbe4e', true);
                            $result = update_option('oembed-github-closed-color', '#cb2431', true);
                            $result = update_option('oembed-github-merged-color', '#6f42c1', true);
                            $result = update_option('oembed-github-stats-secondary-color', '#666666', true);
                            $result = update_option('oembed-github-logo-color', '#666666', true);

                            echo '<h3 style="color: green;">GitHub Theme Configuration has been reset to the default values!</h3>';
                        }
                    }

                    $oembed_github_background_color = get_option('oembed-github-background-color');
                    $oembed_github_background_color = ($oembed_github_background_color === false) ? '#5C7CFA' : $oembed_github_background_color;

                    $oembed_github_top_background_color = get_option('oembed-github-top-background-color');
                    $oembed_github_top_background_color = ($oembed_github_top_background_color === false) ? '#FFFFFF' : $oembed_github_top_background_color;

                    $oembed_github_subheader_background_color = get_option('oembed-github-subheader-background-color');
                    $oembed_github_subheader_background_color = ($oembed_github_subheader_background_color === false) ? '#EEEEEE' : $oembed_github_subheader_background_color;

                    $oembed_github_main_font_color = get_option('oembed-github-main-font-color');
                    $oembed_github_main_font_color = ($oembed_github_main_font_color === false) ? '#000000' : $oembed_github_main_font_color;

                    $oembed_github_sub_title_font_color = get_option('oembed-github-sub-title-font-color');
                    $oembed_github_sub_title_font_color = ($oembed_github_sub_title_font_color === false) ? '#000000' : $oembed_github_sub_title_font_color;

                    $oembed_github_sub_header_font_color = get_option('oembed-github-sub-header-font-color');
                    $oembed_github_sub_header_font_color = ($oembed_github_sub_header_font_color === false) ? '#000000' : $oembed_github_sub_header_font_color;

                    $oembed_github_description_font_color = get_option('oembed-github-description-font-color');
                    $oembed_github_description_font_color = ($oembed_github_description_font_color === false) ? '#000000' : $oembed_github_description_font_color;

                    $oembed_github_stat_header_font_color = get_option('oembed-github-stat-header-font-color');
                    $oembed_github_stat_header_font_color = ($oembed_github_stat_header_font_color === false) ? '#000000' : $oembed_github_stat_header_font_color;

                    $oembed_github_stat_sub_header_font_color = get_option('oembed-github-stat-sub-header-font-color');
                    $oembed_github_stat_sub_header_font_color = ($oembed_github_stat_sub_header_font_color === false) ? '#777777' : $oembed_github_stat_sub_header_font_color;

                    $oembed_github_logo_color = get_option('oembed-github-logo-color');
                    $oembed_github_logo_color = ($oembed_github_logo_color === false) ? '#666666' : $oembed_github_logo_color;

                    $oembed_github_stats_secondary_color = get_option('oembed-github-stats-secondary-color');
                    $oembed_github_stats_secondary_color = ($oembed_github_stats_secondary_color === false) ? '#666666' : $oembed_github_stats_secondary_color;

                    $oembed_github_open_color = get_option('oembed-github-open-color');
                    $oembed_github_open_color = ($oembed_github_open_color === false) ? '#2cbe4e' : $oembed_github_open_color;

                    $oembed_github_closed_color = get_option('oembed-github-closed-color');
                    $oembed_github_closed_color = ($oembed_github_closed_color === false) ? '#cb2431' : $oembed_github_closed_color;

                    $oembed_github_merged_color = get_option('oembed-github-merged-color');
                    $oembed_github_merged_color = ($oembed_github_merged_color === false) ? '#6f42c1' : $oembed_github_merged_color;
                ?>

                <form method="post" action="<?= $submit_url; ?>&submit_theme">
                    <table>
                        <tr>
                            <td>Background Color</td><td>:</td><td><input type="text" name="oembed-github-background-color" onkeyup="oembed_github_update_background_color('oembed-github-background-color-display', this.value)" value="<?= $oembed_github_background_color; ?>" /></td><td><div style="width: 22px;height: 22px;border: 1px solid black;border-radius:11px;background-color: <?= $oembed_github_background_color; ?>;" id="oembed-github-background-color-display"></div></td>
                        </tr>
                        <tr>
                            <td>Top Background Color</td><td>:</td><td><input type="text" name="oembed-github-top-background-color" onkeyup="oembed_github_update_background_color('oembed-github-top-background-color-display', this.value)" value="<?= $oembed_github_top_background_color; ?>" /></td><td><div style="width: 22px;height: 22px;border: 1px solid black;border-radius:11px;background-color: <?= $oembed_github_top_background_color; ?>;" id="oembed-github-top-background-color-display"></div></td>
                        </tr>
                        <tr>
                            <td>Sub Heading Background Color</td><td>:</td><td><input type="text" name="oembed-github-subheader-background-color" onkeyup="oembed_github_update_background_color('oembed-github-subheader-background-color-display', this.value)" value="<?= $oembed_github_subheader_background_color; ?>" /></td><td><div style="width: 22px;height: 22px;border: 1px solid black;border-radius:11px;background-color: <?= $oembed_github_subheader_background_color; ?>;" id="oembed-github-subheader-background-color-display"></div></td>
                        </tr>
                        <tr>
                            <td>Main Font Color</td><td>:</td><td><input type="text" name="oembed-github-main-font-color" onkeyup="oembed_github_update_background_color('oembed-github-main-font-color-display', this.value)" value="<?= $oembed_github_main_font_color; ?>" /></td><td><div style="width: 22px;height: 22px;border: 1px solid black;border-radius:11px;background-color: <?= $oembed_github_main_font_color; ?>;" id="oembed-github-main-font-color-display"></div></td>
                        </tr>
                        <tr>
                            <td>Sub Title Font Color</td><td>:</td><td><input type="text" name="oembed-github-sub-title-font-color" onkeyup="oembed_github_update_background_color('oembed-github-sub-title-font-color-display', this.value)" value="<?= $oembed_github_sub_title_font_color; ?>" /></td><td><div style="width: 22px;height: 22px;border: 1px solid black;border-radius:11px;background-color: <?= $oembed_github_sub_title_font_color; ?>;" id="oembed-github-sub-title-font-color-display"></div></td>
                        </tr>
                        <tr>
                            <td>Sub Header Font Color</td><td>:</td><td><input type="text" name="oembed-github-sub-header-font-color" onkeyup="oembed_github_update_background_color('oembed-github-sub-header-font-color-display', this.value)" value="<?= $oembed_github_sub_header_font_color; ?>" /></td><td><div style="width: 22px;height: 22px;border: 1px solid black;border-radius:11px;background-color: <?= $oembed_github_sub_header_font_color; ?>;" id="oembed-github-sub-header-font-color-display"></div></td>
                        </tr>
                        <tr>
                            <td>Description Font Color</td><td>:</td><td><input type="text" name="oembed-github-description-font-color" onkeyup="oembed_github_update_background_color('oembed-github-description-font-color-display', this.value)" value="<?= $oembed_github_description_font_color; ?>" /></td><td><div style="width: 22px;height: 22px;border: 1px solid black;border-radius:11px;background-color: <?= $oembed_github_description_font_color; ?>;" id="oembed-github-description-font-color-display"></div></td>
                        </tr>
                        <tr>
                            <td>Stat Header Font Color</td><td>:</td><td><input type="text" name="oembed-github-stat-header-font-color" onkeyup="oembed_github_update_background_color('oembed-github-stat-header-font-color-display', this.value)" value="<?= $oembed_github_stat_header_font_color; ?>" /></td><td><div style="width: 22px;height: 22px;border: 1px solid black;border-radius:11px;background-color: <?= $oembed_github_stat_header_font_color; ?>;" id="oembed-github-stat-header-font-color-display"></div></td>
                        </tr>
                        <tr>
                            <td>Stat Sub-Header Font Color</td><td>:</td><td><input type="text" name="oembed-github-stat-sub-header-font-color" onkeyup="oembed_github_update_background_color('oembed-github-stat-sub-header-font-color-display', this.value)" value="<?= $oembed_github_stat_sub_header_font_color; ?>" /></td><td><div style="width: 22px;height: 22px;border: 1px solid black;border-radius:11px;background-color: <?= $oembed_github_stat_sub_header_font_color; ?>;" id="oembed-github-stat-sub-header-font-color-display"></div></td>
                        </tr>
                        <tr>
                            <td>Stats Secondary Color</td><td>:</td><td><input type="text" name="oembed-github-stats-secondary-color" onkeyup="oembed_github_update_background_color('oembed-github-stats-secondary-color-display', this.value)" value="<?= $oembed_github_stats_secondary_color; ?>" /></td><td><div style="width: 22px;height: 22px;border: 1px solid black;border-radius:11px;background-color: <?= $oembed_github_stats_secondary_color; ?>;" id="oembed-github-stats-secondary-color-display"></div></td>
                        </tr>
                        <tr>
                            <td>Open Color</td><td>:</td><td><input type="text" name="oembed-github-open-color" onkeyup="oembed_github_update_background_color('oembed-github-open-color-display', this.value)" value="<?= $oembed_github_open_color; ?>" /></td><td><div style="width: 22px;height: 22px;border: 1px solid black;border-radius:11px;background-color: <?= $oembed_github_open_color; ?>;" id="oembed-github-open-color-display"></div></td>
                        </tr>
                        <tr>
                            <td>Closed Color</td><td>:</td><td><input type="text" name="oembed-github-closed-color" onkeyup="oembed_github_update_background_color('oembed-github-closed-color-display', this.value)" value="<?= $oembed_github_closed_color; ?>" /></td><td><div style="width: 22px;height: 22px;border: 1px solid black;border-radius:11px;background-color: <?= $oembed_github_closed_color; ?>;" id="oembed-github-closed-color-display"></div></td>
                        </tr>
                        <tr>
                            <td>Merged Color</td><td>:</td><td><input type="text" name="oembed-github-merged-color" onkeyup="oembed_github_update_background_color('oembed-github-merged-color-display', this.value)" value="<?= $oembed_github_merged_color; ?>" /></td><td><div style="width: 22px;height: 22px;border: 1px solid black;border-radius:11px;background-color: <?= $oembed_github_merged_color; ?>;" id="oembed-github-merged-color-display"></div></td>
                        </tr>
                        <tr>
                            <td>GitHub Logo Color</td><td>:</td><td><input type="text" name="oembed-github-logo-color" onkeyup="oembed_github_update_background_color('oembed-github-logo-color-display', this.value)" value="<?= $oembed_github_logo_color; ?>" /></td><td><div style="width: 22px;height: 22px;border: 1px solid black;border-radius:11px;background-color: <?= $oembed_github_logo_color; ?>;" id="oembed-github-logo-color-display"></div></td>
                        </tr>
                    </table>
                    <button type="submit" name="submit" id="submit_theme" class="button button-primary"><i class="far fa-save"></i> &nbsp;Save Changes</button>
                </form>
                <br />
                <form method="POST" action="<?= $submit_url; ?>&reset_theme#theme-configuration">
                    <input type="hidden" name="oembed-github-reset-theme" value="1" />
                    <button type="submit" name="submit" id="reset_theme" class="button button-secondary"><i class="fas fa-redo-alt"></i> &nbsp;Reset Theme to Default Settings</button>
                </form>
        <?php
    }

    /**
     * Register the oEmbed provider using this plugin
     * as an oEmbed endpoint.
     */
    public function oEmbedRegisterServices()
    {
        $oEmbedURL = home_url();
        $oEmbedURL = add_query_arg( ['wpgh_oembed' => $this->oEmbedGetKey() ], $oEmbedURL );
        wp_oembed_add_provider( self::WP_GITHUB_PATTERN, $oEmbedURL, true );
        wp_oembed_add_provider( self::WP_GITHUB_GIST_PATTERN, $oEmbedURL, true );
    }

    /**
     * Validates an oEmbed request and parses it.
     */
    public function oEmbedValidateRequest()
    {
        if (isset($_GET['wpgh_oembed'])) {
            if ($_GET['wpgh_oembed'] != $this->oEmbedGetKey()) {
                header( 'HTTP/1.0 403 Forbidden' );
                die('Unauthorized access to GitHub oEmbed endpoint.');
            }

            $this->oEmbed();
        }
    }

    /**
     * Retrieve the currently active oEmbedKey.
     *
     * This is used to avoid external sources from using
     * this wordpress site as a oEmbed endpoint for github.
     * 
     * @return string
     */
    private function oEmbedGetKey()
    {
        $key = get_option('wpgh_oembed_key');
        if (! $key) {
            $key = md5(time().rand( 0, 65535 ));
            add_option('wpgh_oembed_key', $key, '', 'yes');
        }

        return $key;
    }

    /**
     * Performs an oEmbed parse to return oEmbed styled data.
     */
    private function oEmbed()
    {
        // Retrieve and validate the information passed.

        $url = isset($_REQUEST['url']) ? $_REQUEST['url'] : null;
        $format = isset($_REQUEST['format']) ? $_REQUEST['format'] : null;

        if (empty($format) || ($format != 'xml' && $format != 'json')) {
            header( 'HTTP/1.0 501 Not implemented' );
            die( 'GitHub oEmbed endpoint only supports the JSON and XML format.');
        }

        if ( ! $url ) {
            header('HTTP/1.0 404 Not Found');
            die('GitHub: Invalid URL supplied.');
        }

        $parsedUrl = $this->parseGitHubUrl($url);

        if ($parsedUrl === false) {
            header('HTTP/1.0 500 Internal Error');
            die('GitHub: Invalid URL supplied.');
        }

        $this->oEmbedGenerateResult($parsedUrl, $format);
    }

    /**
     * Generate oEmbed result data in JSON format.
     * 
     * @param object $parsedUrl
     * @param string $format
     */
    private function oEmbedGenerateResult($parsedUrl, $format)
    {
        $data = $this->fetchGitHubInformation($parsedUrl);
        if (is_null($data)) {
            header('HTTP/1.0 500 Internal Error');
            die('GitHub: An error occurred while communicating with the GitHub API.');
        }

        $data = json_decode($data['body'], true);

        // Theme Options
        $oembed_github_background_color = get_option('oembed-github-background-color');
        $oembed_github_background_color = ($oembed_github_background_color === false) ? '#5C7CFA' : $oembed_github_background_color;

        $oembed_github_top_background_color = get_option('oembed-github-top-background-color');
        $oembed_github_top_background_color = ($oembed_github_top_background_color === false) ? '#FFFFFF' : $oembed_github_top_background_color;

        $oembed_github_subheader_background_color = get_option('oembed-github-subheader-background-color');
        $oembed_github_subheader_background_color = ($oembed_github_subheader_background_color === false) ? '#EEEEEE' : $oembed_github_subheader_background_color;

        $oembed_github_main_font_color = get_option('oembed-github-main-font-color');
        $oembed_github_main_font_color = ($oembed_github_main_font_color === false) ? '#000000' : $oembed_github_main_font_color;

        $oembed_github_sub_title_font_color = get_option('oembed-github-sub-title-font-color');
        $oembed_github_sub_title_font_color = ($oembed_github_sub_title_font_color === false) ? '#000000' : $oembed_github_sub_title_font_color;

        $oembed_github_sub_header_font_color = get_option('oembed-github-sub-header-font-color');
        $oembed_github_sub_header_font_color = ($oembed_github_sub_header_font_color === false) ? '#000000' : $oembed_github_sub_header_font_color;

        $oembed_github_description_font_color = get_option('oembed-github-description-font-color');
        $oembed_github_description_font_color = ($oembed_github_description_font_color === false) ? '#000000' : $oembed_github_description_font_color;

        $oembed_github_stat_header_font_color = get_option('oembed-github-stat-header-font-color');
        $oembed_github_stat_header_font_color = ($oembed_github_stat_header_font_color === false) ? '#000000' : $oembed_github_stat_header_font_color;

        $oembed_github_stat_sub_header_font_color = get_option('oembed-github-stat-sub-header-font-color');
        $oembed_github_stat_sub_header_font_color = ($oembed_github_stat_sub_header_font_color === false) ? '#777777' : $oembed_github_stat_sub_header_font_color;

        $oembed_github_logo_color = get_option('oembed-github-logo-color');
        $oembed_github_logo_color = ($oembed_github_logo_color === false) ? '#666666' : $oembed_github_logo_color;

        $oembed_github_stats_secondary_color = get_option('oembed-github-stats-secondary-color');
        $oembed_github_stats_secondary_color = ($oembed_github_stats_secondary_color === false) ? '#666666' : $oembed_github_stats_secondary_color;

        $oembed_github_open_color = get_option('oembed-github-open-color');
        $oembed_github_open_color = ($oembed_github_open_color === false) ? '#2cbe4e' : $oembed_github_open_color;

        $oembed_github_closed_color = get_option('oembed-github-closed-color');
        $oembed_github_closed_color = ($oembed_github_closed_color === false) ? '#cb2431' : $oembed_github_closed_color;

        $oembed_github_merged_color = get_option('oembed-github-merged-color');
        $oembed_github_merged_color = ($oembed_github_merged_color === false) ? '#6f42c1' : $oembed_github_merged_color;

        // Initial object
        $response = new stdClass();
        $response->version = '1.0';
        $response->type = 'rich';
        $response->width = '10';
        $response->height = '10';

        // TODO: Known bug, if any of the content has a single quote in it
        //       it will break javascript.

        // Parse the URL into the oEmbed object.
        switch($parsedUrl->type) {
            case 'gist' : {
                $file = null;
                foreach ($data['files'] as $filename => $file_inner) {
                    $file = $file_inner;
                    break;
                }

                if ($file == null) {
                    die('No file found for gist.');
                }

                $randomId = rand(0, 9999999);

                $content = '
                    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
                    <body>
                        <div align="center">
                            <div align="left" style="padding: 10px;background: '.$oembed_github_background_color.';width: 545px;border-radius: 10px;">
                                <div style="background: '.$oembed_github_top_background_color.';box-shadow: rgba(0, 0, 0, 0.1) 0px 4px 0px 0px;position: relative;font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Helvetica,Arial,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol;border: 0px none rgb(73, 80, 87);border-radius: 8px 8px 8px 8px;padding: 10px;width: 525px;height:auto;">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="width: 65px;">
                                                <img style="width: 55px; height: 55px;border-radius:38px;float: left;margin-right: 5px;" src="'.$data['owner']['avatar_url'].'" />
                                            </td>
                                            <td>
                                                <h3 style="margin-top: 0px;margin-bottom: 5px;"><b><a style="text-decoration: none;color: '.$oembed_github_main_font_color.';font-size: 18px !important; font-weight: bold !important; text-decoration: none !important;" href="'.$data['html_url'].'" target="_blank">'.$file['filename'].'</a></b></h3>
                                                <a class="github-button" href="'.$data['html_url'].'" data-icon="octicon-star" data-size="large" aria-label="Star on GitHub">Star</a>
                                            </td>
                                            <td style="text-align: center;width: 53px;" valign="middle">
                                                <i style="font-size: 28px;color: '.$oembed_github_logo_color.';" class="fab fa-github"></i>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="margin-top: 0px;margin-left: 10px;width: 525px;height: auto;border-bottom-right-radius: 8px;border-bottom-left-radius: 8px;background-color: #FFFFFF;font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Helvetica,Arial,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol;">
                                    <code data-gist-id="'.$data['id'].'"></code>
                                </div>
                            </div>
                        </div>
                    </body>
                ';

                $response->html = '
                    <script>
                        function resizeIframe(obj) {
                            obj.style.height = obj.contentWindow.document.body.scrollHeight + "px";
                        }
                    </script>
                    <iframe frameborder="0" scrolling="no" onload="resizeIframe(this);" id="'.$randomId.'" style="width: 100%;margin: 0px !important;padding: 0px !important;display: block !important;border: 0px !important;"></iframe>
                    
                    <script>
                        var doc = document.getElementById("'.$randomId.'").contentWindow.document;
                        doc.open();
                        doc.write(\'<script async defer src="https://buttons.github.io/buttons.js">\');
                        doc.write(\'</script\');
                        doc.write(\'>\');
                        doc.write(\'<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js">\');
                        doc.write(\'</script\');
                        doc.write(\'>\');
                        doc.write(\'<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/gist-embed/2.7.1/gist-embed.min.js">\');
                        doc.write(\'</script\');
                        doc.write(\'>\');
                        doc.write(\''.str_replace("'", "\\'", $content).'\');
                        doc.close();
                    </script>
                ';

                break;
            }
    
            case 'profile' : {
                $repos_count = $this->niceNumber($data['public_repos']);
                $follower_count = $this->niceNumber($data['followers']);
                $gists_count = $this->niceNumber($data['public_gists']);

                $randomId = rand(0, 9999999);

                $content = '
                    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
                    <body>
                        <div align="center">
                            <div align="left" style="padding: 10px;background: '.$oembed_github_background_color.';width: 345px;border-radius: 10px;">
                                <div style="background: '.$oembed_github_top_background_color.';box-shadow: rgba(0, 0, 0, 0.1) 0px 4px 0px 0px;position: relative;font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Helvetica,Arial,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol;border: 0px none rgb(73, 80, 87);border-radius: 8px 8px 8px 8px;padding: 10px;width: 325px;height:auto;">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="width: 65px;">
                                                <img style="width: 55px; height: 55px;border-radius:38px;float: left;margin-right: 5px;" src="'.$data['avatar_url'].'" />
                                            </td>
                                            <td>
                                                <h3 style="margin-top: 0px;margin-bottom: 5px;"><b><a style="text-decoration: none;color: '.$oembed_github_main_font_color.';font-size: 18px !important; font-weight: bold !important; text-decoration: none !important;" href="https://github.com/'.$data['login'].'/" target="_blank">'.$data['name'].'</a></b></h3>
                                                <a class="github-button" href="https://github.com/'.$data['login'].'" data-size="large" aria-label="Follow @'.$data['login'].' on GitHub">Follow @'.$data['login'].'</a>
                                            </td>
                                            <td style="text-align: center;" valign="middle">
                                                <i style="font-size: 28px;color: '.$oembed_github_logo_color.';" class="fab fa-github"></i>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="margin-top: 0px;margin-left: 10px;width: 325px;height: 50px;border-bottom-right-radius: 8px;border-bottom-left-radius: 8px;background-color: '.$oembed_github_subheader_background_color.';font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Helvetica,Arial,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol;">
                                    <table style="width: 100%;height: 100%;">
                                        <tr>
                                            <td style="padding: 5px;text-align: center;width: 33%;">
                                                <h4 style="margin: 0px;color: '.$oembed_github_stat_header_font_color.';">'.$repos_count.'</h4>
                                                <h5 style="margin: 0px;margin-top: 5px !important;font-size: 10px;color: '.$oembed_github_stat_sub_header_font_color.';"><i class="fas fa-book"></i> REPOS</h5>
                                            </td>
                                            <td style="padding: 5px;text-align: center;width: 33%;">
                                                <h4 style="margin: 0px;color: '.$oembed_github_stat_header_font_color.';">'.$gists_count.'</h4>
                                                <h5 style="margin: 0px;margin-top: 5px !important;font-size: 10px;color: '.$oembed_github_stat_sub_header_font_color.';"><i class="fab fa-github-square"></i> GISTS</h5>
                                            </td>
                                            <td style="padding: 5px;text-align: center;width: 33%;">
                                                <h4 style="margin: 0px;color: '.$oembed_github_stat_header_font_color.';">'.$follower_count.'</h4>
                                                <h5 style="margin: 0px;margin-top: 5px !important;font-size: 10px;color: '.$oembed_github_stat_sub_header_font_color.';"><i class="fas fa-walking"></i> FOLLOWERS</h5>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </body>
                ';

                $response->html = '
                    <script>
                        function resizeIframe(obj) {
                            obj.style.height = obj.contentWindow.document.body.scrollHeight + "px";
                        }
                    </script>
                    <iframe frameborder="0" scrolling="no" onload="resizeIframe(this);" id="'.$randomId.'" style="width: 100%;margin: 0px !important;padding: 0px !important;display: block !important;border: 0px !important;"></iframe>
                    <script>
                        var doc = document.getElementById("'.$randomId.'").contentWindow.document;
                        doc.open();
                        doc.write(\'<script async defer src="https://buttons.github.io/buttons.js">\');
                        doc.write(\'</script\');
                        doc.write(\'>\');
                        doc.write(\''.str_replace("'", "\\'", $content).'\');
                        doc.close();
                    </script>
                ';
                break;
            }
    
            case 'repository' : {
                $forks_count = $this->niceNumber($data['forks']);
                $watchers_count = $this->niceNumber($data['subscribers_count']);
                $stars_count = $this->niceNumber($data['stargazers_count']);

                $randomId = rand(0, 9999999);

                $content = '
                    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
                    <body>
                        <div align="center">
                            <div align="left" style="padding: 10px;background: '.$oembed_github_background_color.';width: 395px;border-radius: 10px;">
                                <div style="background: '.$oembed_github_top_background_color.';box-shadow: rgba(0, 0, 0, 0.1) 0px 4px 0px 0px;position: relative;font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Helvetica,Arial,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol;border: 0px none rgb(73, 80, 87);border-radius: 8px 8px 8px 8px;padding: 10px;width: 375px;height:auto;">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="width: 65px;">
                                                <img style="width: 55px; height: 55px;border-radius:38px;float: left;margin-right: 5px;" src="'.$data['owner']['avatar_url'].'" />
                                            </td>
                                            <td>
                                                <h3 style="margin-top: 0px;margin-bottom: 5px;"><b><a style="text-decoration: none;color: '.$oembed_github_main_font_color.';font-size: 18px !important; font-weight: bold !important; text-decoration: none !important;" href="'.$data['html_url'].'" target="_blank">'.$data['full_name'].'</a></b></h3>
                                                <h4 style="margin-top: 0px;margin-bottom: 10px;color: '.$oembed_github_description_font_color.';font-size: 11px !important;font-weight: lighter;">'.$data['description'].'</h4>
                                                <a class="github-button" href="'.$data['html_url'].'" data-icon="octicon-star" data-size="large" aria-label="Star '.$data['full_name'].' on GitHub">Star</a>
                                                <a class="github-button" href="'.$data['html_url'].'/subscription" data-icon="octicon-eye" data-size="large" aria-label="Watch '.$data['full_name'].' on GitHub">Watch</a>
                                            </td>
                                            <td style="text-align: center;" valign="middle">
                                                <i style="font-size: 28px;color: '.$oembed_github_logo_color.';" class="fab fa-github"></i>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="margin-top: 0px;margin-left: 10px;width: 375px;height: 50px;border-bottom-right-radius: 8px;border-bottom-left-radius: 8px;background-color: '.$oembed_github_subheader_background_color.';font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Helvetica,Arial,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol;">
                                    <table style="width: 100%;height: 100%;">
                                        <tr>
                                            <td style="padding: 5px;text-align: center;width: 33%;">
                                                <h4 style="margin: 0px;color: '.$oembed_github_stat_header_font_color.';">'.$forks_count.'</h4>
                                                <h5 style="margin: 0px;margin-top: 5px !important;font-size: 10px;color: '.$oembed_github_stat_sub_header_font_color.';"><i class="fas fa-code-branch"></i> FORKS</h5>
                                            </td>
                                            <td style="padding: 5px;text-align: center;width: 33%;">
                                                <h4 style="margin: 0px;color: '.$oembed_github_stat_header_font_color.';">'.$stars_count.'</h4>
                                                <h5 style="margin: 0px;margin-top: 5px !important;font-size: 10px;color: '.$oembed_github_stat_sub_header_font_color.';"><i class="fas fa-star"></i> STARS</h5>
                                            </td>
                                            <td style="padding: 5px;text-align: center;width: 33%;">
                                                <h4 style="margin: 0px;color: '.$oembed_github_stat_header_font_color.';">'.$watchers_count.'</h4>
                                                <h5 style="margin: 0px;margin-top: 5px !important;font-size: 10px;color: '.$oembed_github_stat_sub_header_font_color.';"><i class="fas fa-eye"></i> WATCHERS</h5>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </body>
                ';

                $response->html = '
                    <script>
                        function resizeIframe(obj) {
                            obj.style.height = obj.contentWindow.document.body.scrollHeight + "px";
                        }
                    </script>
                    <iframe frameborder="0" scrolling="no" onload="resizeIframe(this);" id="'.$randomId.'" style="width: 100%;height: 225px;width: 100%;margin: 0px !important;padding: 0px !important;display: block !important;border: 0px !important;"></iframe>
                    <script>
                        var doc = document.getElementById("'.$randomId.'").contentWindow.document;
                        doc.open();
                        doc.write(\'<script async defer src="https://buttons.github.io/buttons.js">\');
                        doc.write(\'</script\');
                        doc.write(\'>\');
                        doc.write(\''.str_replace("'", "\\'", $content).'\');
                        doc.close();
                    </script>
                ';
                break;
            }
    
            case 'issues' : {
                $randomId = rand(0, 9999999);

                $content = '
                    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
                    <body>
                        <div align="center">
                            <div align="left" style="padding: 10px;background: '.$oembed_github_background_color.';width: 445px;border-radius: 10px;">
                                <div style="background: '.$oembed_github_top_background_color.';box-shadow: rgba(0, 0, 0, 0.1) 0px 4px 0px 0px;position: relative;font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Helvetica,Arial,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol;border: 0px none rgb(73, 80, 87);border-radius: 8px 8px 8px 8px;padding: 10px;width: 425px;height:auto;">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="width: 65px;">
                                                <img style="width: 55px; height: 55px;border-radius:38px;float: left;margin-right: 5px;" src="'.$data['user']['avatar_url'].'" />
                                            </td>
                                            <td>
                                                <h3 style="margin-top: 0px;margin-bottom: 0px;"><b><a style="text-decoration: none;color: '.$oembed_github_main_font_color.';font-size: 18px !important; font-weight: bold !important; text-decoration: none !important;" href="'.$data['html_url'].'" target="_blank">'.$data['title'].' <span style="color: #888;">#'.$data['number'].'</span></a></b></h3>
                                                <h4 style="margin-top: 0px;margin-bottom: 10px;"><a style="text-decoration: none;color: '.$oembed_github_sub_title_font_color.';font-size: 18px !important; text-decoration: none !important;font-weight: lighter;" href="'.$data['html_url'].'" target="_blank">'.$parsedUrl->username.'/'.$parsedUrl->repository.'</a></h4>
                                                '.
                                                    (
                                                        ($data['state'] == 'open')
                                                            ? '<h4 style="margin-top: 0px;margin-bottom: 10px;"><span style="padding: 5px;background-color: '.$oembed_github_open_color.';color: white;border-radius: 5px;font-size: 12px;"><i class="fas fa-exclamation-circle"></i> Open</span></h4>'
                                                            : '<h4 style="margin-top: 0px;margin-bottom: 10px;"><span style="padding: 5px;background-color: '.$oembed_github_closed_color.';color: white;border-radius: 5px;font-size: 12px;"><i class="fas fa-check-circle"></i> Closed</span></h4>'
                                                    )
                                                .'
                                            </td>
                                            <td style="text-align: center;" valign="middle">
                                                <i style="font-size: 28px;color: '.$oembed_github_logo_color.';" class="fab fa-github"></i>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </body>
                ';

                $response->html = '
                    <script>
                        function resizeIframe(obj) {
                            obj.style.height = obj.contentWindow.document.body.scrollHeight + "px";
                        }
                    </script>
                    <iframe frameborder="0" scrolling="no" onload="resizeIframe(this);" id="'.$randomId.'" style="width: 100%;margin: 0px !important;padding: 0px !important;display: block !important;border: 0px !important;"></iframe>
                    
                    <script>
                        var doc = document.getElementById("'.$randomId.'").contentWindow.document;
                        doc.open();
                        doc.write(\'<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js">\');
                        doc.write(\'</script\');
                        doc.write(\'>\');
                        doc.write(\''.str_replace("'", "\\'", $content).'\');
                        doc.close();
                    </script>
                ';

                break;
            }
    
            case 'commit' : {
                $randomId = rand(0, 9999999);

                $messageLines = explode("\n", $data['commit']['message']);
                $firstMessage = $messageLines[0];
                $restOfMessage = '';
                for($i=1;$i<count($messageLines);$i++) {
                    if (trim($messageLines[$i]) != '') {
                        $restOfMessage .= '<li>'.$messageLines[$i]."</li>";
                    }
                }

                $content = '
                    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
                    <body>
                        <div align="center">
                            <div align="left" style="padding: 10px;background: '.$oembed_github_background_color.';width: 445px;border-radius: 10px;">
                                <div style="background: '.$oembed_github_top_background_color.';box-shadow: rgba(0, 0, 0, 0.1) 0px 4px 0px 0px;position: relative;font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Helvetica,Arial,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol;border: 0px none rgb(73, 80, 87);border-radius: 8px 8px 8px 8px;padding: 10px;width: 425px;height:auto;">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="width: 65px;">
                                                <img style="width: 55px; height: 55px;border-radius:38px;float: left;margin-right: 5px;" src="'.$data['author']['avatar_url'].'" />
                                            </td>
                                            <td>
                                                <h3 style="margin-top: 0px;margin-bottom: 0px;"><b><a style="text-decoration: none;color: '.$oembed_github_main_font_color.';font-size: 18px !important; font-weight: bold !important; text-decoration: none !important;" href="'.$data['html_url'].'" target="_blank">'.$firstMessage.'</a></b></h3>
                                                <h4 style="margin-top: 0px;margin-bottom: 10px;"><a style="text-decoration: none;color: '.$oembed_github_sub_title_font_color.';font-size: 18px !important; text-decoration: none !important;font-weight: lighter;" href="'.$data['html_url'].'" target="_blank"'.$parsedUrl->username.'/'.$parsedUrl->repository.'</a></h4>
                                                <h5 style="margin-top: 5px;margin-bottom: 0px;"><span style="color: '.$oembed_github_stats_secondary_color.';"><i class="fas fa-plus-circle"></i> '.$data['stats']['additions'].' additions</span>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: '.$oembed_github_stats_secondary_color.';"><i class="fas fa-minus-circle"></i> '.$data['stats']['deletions'].' deletions</span></h5>
                                                
                                            </td>
                                            <td style="text-align: center;" valign="middle">
                                                <i style="font-size: 28px;color: '.$oembed_github_logo_color.';" class="fab fa-github"></i>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                '.
                                (
                                    count($messageLines) > 1
                                        ? '
                                            <div style="margin-top: 0px;margin-left: 10px;width: 425px;height: auto;border-bottom-right-radius: 8px;border-bottom-left-radius: 8px;color: '.$oembed_github_sub_header_font_color.';background-color: '.$oembed_github_subheader_background_color.';font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Helvetica,Arial,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol;">
                                                <ul style="margin-right: 15px;margin-top: 0px;margin-bottom: 5px;padding-top: 15px;padding-bottom: 15px;">
                                                    '.$restOfMessage.'
                                                </ul>
                                            </div>
                                        '
                                        : ''
                                ).
                                '
                            </div>
                        </div>
                    </body>
                ';

                $response->html = '
                    <script>
                        function resizeIframe(obj) {
                            obj.style.height = obj.contentWindow.document.body.scrollHeight + "px";
                        }
                    </script>
                    <iframe frameborder="0" scrolling="no" onload="resizeIframe(this);" id="'.$randomId.'" style="width: 100%;margin: 0px !important;padding: 0px !important;display: block !important;border: 0px !important;"></iframe>
                    
                    <script>
                        var doc = document.getElementById("'.$randomId.'").contentWindow.document;
                        doc.open();
                        doc.write(\'<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js">\');
                        doc.write(\'</script\');
                        doc.write(\'>\');
                        doc.write(\''.str_replace("'", "\\'", $content).'\');
                        doc.close();
                    </script>
                ';

                break;
            }
    
            case 'pull' : {
                $randomId = rand(0, 9999999);

                $content = '
                    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
                    <body>
                        <div align="center">
                            <div align="left" style="padding: 10px;background: '.$oembed_github_background_color.';width: 445px;border-radius: 10px;">
                                <div style="background: '.$oembed_github_top_background_color.';box-shadow: rgba(0, 0, 0, 0.1) 0px 4px 0px 0px;position: relative;font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Helvetica,Arial,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol;border: 0px none rgb(73, 80, 87);border-radius: 8px 8px 8px 8px;padding: 10px;width: 425px;height:auto;">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="width: 65px;">
                                                <img style="width: 55px; height: 55px;border-radius:38px;float: left;margin-right: 5px;" src="'.$data['user']['avatar_url'].'" />
                                            </td>
                                            
                                            <td>
                                                <h3 style="margin-top: 0px;margin-bottom: 0px;"><b><a style="text-decoration: none;color: '.$oembed_github_main_font_color.';font-size: 18px !important; font-weight: bold !important; text-decoration: none !important;" href="'.$data['html_url'].'" target="_blank">'.$data['title'].' <span style="color: #888;">#'.$data['number'].'</span></a></b></h3>
                                                <h4 style="margin-top: 0px;margin-bottom: 10px;"><a style="text-decoration: none;color: '.$oembed_github_sub_title_font_color.';font-size: 18px !important; text-decoration: none !important;font-weight: lighter;" href="'.$data['html_url'].'" target="_blank">'.$parsedUrl->username.'/'.$parsedUrl->repository.'</a></h4>
                                                <h5 style="margin-top: 5px;margin-bottom: 0px;">'.
                                                (
                                                    $data['state'] == 'closed'
                                                        ? '<span style="padding: 5px;background-color: '.$oembed_github_merged_color.';color: white;border-radius: 5px;font-size: 12px;"><i class="fas fa-check-circle"></i> Merged</span>'
                                                        : '<span style="padding: 5px;background-color: '.$oembed_github_open_color.';color: white;border-radius: 5px;font-size: 12px;"><i class="fas fa-check-circle"></i> Open</span>'
                                                )
                                                .'&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: '.$oembed_github_stats_secondary_color.';"><i class="fas fa-code-branch"></i> '.$data['head']['ref'].' <i class="fas fa-arrow-right"></i> '.$data['base']['ref'].'</span></h5>
                                                
                                            </td>
                                            <td style="text-align: center;" valign="middle">
                                                <i style="font-size: 28px;color: '.$oembed_github_logo_color.';" class="fab fa-github"></i>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="margin-top: 0px;margin-left: 10px;width: 425px;height: 50px;border-bottom-right-radius: 8px;border-bottom-left-radius: 8px;background-color: '.$oembed_github_subheader_background_color.';font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Helvetica,Arial,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol;">
                                    <table style="width: 100%;height: 100%;">
                                        <tr>
                                            <td style="padding: 5px;text-align: center;width: 50%;">
                                                <h4 style="margin: 0px;color: '.$oembed_github_stat_header_font_color.';">'.$data['commits'].'</h4>
                                                <h5 style="margin: 0px;margin-top: 5px !important;font-size: 10px;color: '.$oembed_github_stat_sub_header_font_color.';"><i class="fas fa-code"></i> COMMITS</h5>
                                            </td>
                                            <td style="padding: 5px;text-align: center;width: 50%;">
                                                <h4 style="margin: 0px;color: '.$oembed_github_stat_header_font_color.';">'.$data['changed_files'].'</h4>
                                                <h5 style="margin: 0px;margin-top: 5px !important;font-size: 10px;color: '.$oembed_github_stat_sub_header_font_color.';"><i class="far fa-file"></i> FILES CHANGED</h5>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </body>
                ';

                $response->html = '
                    <script>
                        function resizeIframe(obj) {
                            obj.style.height = obj.contentWindow.document.body.scrollHeight + "px";
                        }
                    </script>
                    <iframe frameborder="0" scrolling="no" onload="resizeIframe(this);" id="'.$randomId.'" style="width: 100%;margin: 0px !important;padding: 0px !important;display: block !important;border: 0px !important;"></iframe>
                    
                    <script>
                        var doc = document.getElementById("'.$randomId.'").contentWindow.document;
                        doc.open();
                        doc.write(\'<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js">\');
                        doc.write(\'</script\');
                        doc.write(\'>\');
                        doc.write(\''.str_replace("'", "\\'", $content).'\');
                        doc.close();
                    </script>
                ';

                break;
            }
        }
        
        // Output the response.
        if ($format == 'json') {
            header('Content-Type: application/json');
            echo json_encode($response);
        } else if ($format == 'xml') {
            header('Content-Type: text/xml');
            echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?><oembed>';
            echo '<version>'.$response->version.'</version>';
            echo '<type>'.$response->type.'</type>';
            echo '<width>'.$response->width.'</width>';
            echo '<height>'.$response->height.'</height>';
            echo '<title>'.$response->title.'</title>';
            echo '<html>'.$response->html.'</html>';
            echo '</oembed>';
        }

        die();
    }

    /**
     * ==========================================================================
     * URL Parsing: Regex Patterns for matching against URLs.
     * ==========================================================================
     */

    /**
     * The pattern for parsing github.com URLs.
     * 
     * Supports http and https.
     * Supports www. and non-www.
     * 
     * 
     * Group  7 = User Name
     * Group 10 = Repository
     * Group 12 = Type (issues, commit, pull)
     * Group 14 = Item (issue ID, commit ID, pull ID)
     * Group 16 = Type (commits (only for pull))
     * Group 18 = Commit ID (only for pull)
     * 
     * @var string
     */
    const WP_GITHUB_PATTERN      = "#^((http(s|)):\/\/|)(www.|)github.com(\/)(([a-z0-9-?&%_=]*))((\/([a-z0-9-?&%_=]*))|)(\/([a-z0-9-?&%_=]*)|)(\/([a-z0-9-?&%_=]*)|)(\/([a-z0-9-?&%_=]*)|)(\/([a-z0-9-?&%_=]*)|)#i";

    /**
     * The pattern for parsing gist.github.com URLs.
     * 
     * Group 4 = User Name
     * Group 5 = Gist ID
     * 
     * @var string
     */
    const WP_GITHUB_GIST_PATTERN = "#^((http(s|)):\/\/|)gist.github.com\/([a-z0-9-?&%_=]*)\/([a-z0-9-?&%_=]*)(\/|)#i";

    /**
     * ==========================================================================
     * URL Parsing: Functions for parsing a URL into a detailed object
     *              that can be used to generate the template.
     * ==========================================================================
     */

    /**
     * Parses a GitHub URL into an object containing it's pieces.
     * 
     * @param string $url
     * 
     * @return object|false
     */
    private function parseGitHubUrl($url)
    {
        $pregresult = [];
        $result = [];

        if (! preg_match(self::WP_GITHUB_PATTERN, $url, $pregresult)) {
            if (! preg_match(self::WP_GITHUB_GIST_PATTERN, $url, $pregresult)) {
                return false;
            }
            $result['type'] = 'gist';
        }

        // See WP_GITHUB_PATTER and WP_GITHUB_GIST_PATTERN
        // definitions for information on which regex
        // group belongs to which item.

        if ($result['type'] == 'gist') {
            $result['username'] = $pregresult[4];
            $result['gist_id'] = $pregresult[5];
        } else {
            $result['username'] = $pregresult[7];
            $result['type'] = 'profile';

            if (! empty($pregresult[10])) {
                $result['repository'] = $pregresult[10];
                $result['type'] = 'repository';

                if ($pregresult[12] == 'issues') {
                    $result['issue_id'] = $pregresult[14];
                    $result['type'] = $pregresult[12];
                } else if ($pregresult[12] == 'commit') {
                    $result['commit_id'] = $pregresult[14];
                    $result['type'] = $pregresult[12];
                } else if ($pregresult[12] == 'pull') {
                    $result['pull_id'] = $pregresult[14];
                    $result['type'] = $pregresult[12];
                    if ($pregresult[16] == 'commits') {
                        $result['type'] = 'commit';
                        $result['commit_id'] = $pregresult[18];
                    }
                }
            }
        }

        return (object)$result;
    }

    /**
     * Communicate with the GitHub API to retrieve information
     * about a specified URL.
     * 
     * First parse your URL using the parseGitHubUrl function.
     * 
     * @param object $parsedGitHubUrl
     * 
     * @return object
     */
    private function fetchGitHubInformation($parsedGitHubUrl)
    {
        // Possible Types: gist, profile, repository, issues, commit, pull
        switch ($parsedGitHubUrl->type) {
            case 'gist' : {
                return $this->submitApiRequest(
                    'https://api.github.com/gists/'.
                    $parsedGitHubUrl->gist_id
                );
            }

            case 'profile' : {
                return $this->submitApiRequest(
                    'https://api.github.com/users/'.
                    $parsedGitHubUrl->username
                );
            }

            case 'repository' : {
                return $this->submitApiRequest(
                    'https://api.github.com/repos/'.
                    $parsedGitHubUrl->username.'/'.
                    $parsedGitHubUrl->repository
                );
            }

            case 'issues' : {
                return $this->submitApiRequest(
                    'https://api.github.com/repos/'.
                    $parsedGitHubUrl->username.'/'.
                    $parsedGitHubUrl->repository.'/'.
                    'issues'.'/'.
                    $parsedGitHubUrl->issue_id
                );
            }

            case 'commit' : {
                return $this->submitApiRequest(
                    'https://api.github.com/repos/'.
                    $parsedGitHubUrl->username.'/'.
                    $parsedGitHubUrl->repository.'/'.
                    'commits'.'/'.
                    $parsedGitHubUrl->commit_id
                );
            }

            case 'pull' : {
                return $this->submitApiRequest(
                    'https://api.github.com/repos/'.
                    $parsedGitHubUrl->username.'/'.
                    $parsedGitHubUrl->repository.'/'.
                    'pulls'.'/'.
                    $parsedGitHubUrl->pull_id
                );
            }
        }

        return null;
    }

    /**
     * ==========================================================================
     * GitHub API : Functions for communicating with the GitHub API.
     * ==========================================================================
     */

    /**
     * Submits an API request.
     * 
     * Will automatically append authentication information
     * if any is currently configured.
     * 
     * @param string $request
     * 
     * @return object
     */
    private function submitApiRequest($request)
    {
        $url = $request;

        // TODO: Parse this information from Settings.
        $auth = (object) [
            'type' => 'oauth_client_id',
            'oauth_token' => '',
            'oauth_client_id' => (get_option('oembed-github-client-id') !== false) ? get_option('oembed-github-client-id') : '',
            'oauth_client_secret' => (get_option('oembed-github-client-secret') !== false) ? get_option('oembed-github-client-secret') : '',
        ];

        // Allow users to supply auth details to enable a higher rate limit
        if ($auth->type == 'oauth_token') {
            $url = add_query_arg(
                [
                    'access_token' => $auth->oauth_token,
                ], $request
            );
        } else if ($auth->type == 'oauth_client_id') {
            $url = add_query_arg(
                [
                    'client_id' => $auth->oauth_client_id,
                    'client_secret' => $auth->oauth_client_secret,
                ], $request
            );
        }

        $args = [
            'user-agent' => 'oEmbed GitHub '.
                            '(https://github.com/nathan-fiscaletti/oembed-github)'
        ];
        $results = wp_remote_get( $url, $args );

        if( is_wp_error( $results ) ||
            ! isset( $results['response']['code'] ) ||
            $results['response']['code'] != '200' ) {
                header( 'HTTP/1.0 404 Not Found' );
                die( 'Request to api.github.com failed.' );
        }

        return $results;
    }

    /**
     * Force the API timeout to be no less than 25 seconds.
     * 
     * @param int $seconds
     * 
     * @return int
     */
    public function oEmbedApiTimeout($seconds)
    {
        return $seconds < 25 ? 25 : $seconds;
    }

    /**
     * Converts a number to a nicer format.
     * 
     * @param int $n
     * 
     */
    private function niceNumber($n)
    {
        // first strip any formatting;
        $n = (0+str_replace(",", "", $n));

        // is this a number?
        if (!is_numeric($n)) return false;

        // now filter it;
        if ($n > 1000000000000) return round(($n/1000000000000), 2).'T';
        elseif ($n > 1000000000) return round(($n/1000000000), 2).'G';
        elseif ($n > 1000000) return round(($n/1000000), 2).'M';
        elseif ($n > 1000) return round(($n/1000), 2).'k';

        return number_format($n);
    }
}

/**
 * Initialize the application.
 */

(new oEmbedGitHub())->run();

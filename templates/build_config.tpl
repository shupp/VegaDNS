<form id="config-builder-form" action="{$php_self}" method="post">
    <input type="hidden" name="build_config" value="true">
    <div class="row">
        <div class="small-12 medium-8 small-centered columns">
            <div class="row">
                <div class="small-12 columns">
                    <h3>Build configuration file</h3>
                    <p>(Step 5 from the <a href="INSTALL" target="_blank">INSTALL file</a>)</p>
                    <p>Use this form to set up your configuration file automatically. Alternatively, copy /src/config-sample.php to
                    /src/config.php and edit directly within the file.</p>
                    <p>Ensure you have followed the earlier steps from the <a href="INSTALL" target="_blank">INSTALL file</a> before using this form.</p>
                    <p><strong>IMPORTANT</strong>: Submitting form this on a running live system will overwrite your config file!</p>
                </div>
            </div>
            <div class="row">
                <div class="small-12 columns">
                    <label for="private_dirs">
                        Location of vegadns private directories (should be in ServerRoot of Apache)<br>
                        MUST be writable by the Apache user!
                        <input id="private_dirs" type=text name="private_dirs" value="/usr/local/apache/vegadns" class="required" />
                    </label>
                </div>
                <div class="small-12 columns">
                    <label for="sessions_dir">
                        Location of the sessions directory. A subdirectory of the private directories location is ideal.<br/>
                        <input id="sessions_dir" type="text" name="sessions_dir" value="/usr/local/apache/sessions" class="required" />
                    </label>
                </div>
                <div class="small-12 columns">
                    <label for="mysql_host">
                        MySQL Host (append :port_number if not standard 3306) - default is localhost<br/>
                        <input id="mysql_host" type="text" name="mysql_host" value="localhost" class="required"/>
                    </label>
                </div>
                <div class="small-12 columns">
                    <label for="mysql_user">
                        MySQL Username<br/>
                        <input id="mysql_user" type="text" name="mysql_user" value="vegadns" class="required "/>
                    </label>
                </div>
                <div class="small-12 columns">
                    <label for="mysql_pass">
                        MySQL Password<br/>
                        <input id="mysql_pass" type="text" name="mysql_pass" value="secret" class="required" />
                    </label>
                </div>
                <div class="small-12 columns">
                    <label for="mysql_db">
                        MySQL Database Name<br/>
                        <input id="mysql_db" type="text" name="mysql_db" value="vegadns" class="required" />
                    </label>
                </div>
                <div class="small-12 columns">
                    <label for="local_url">
                        Local URL<br/>
                        <input id="local_url" type="text" name="local_url" value="http://127.0.0.1" class="required" />
                    </label>
                </div>
                <div class="small-12 medium-6 columns">
                    <label for="support_name">
                        Contact name for inactive domains<br/>
                        <input id="support_name" type="text" name="support_name" value="The VegaDNS Team" class="required" />
                    </label>
                </div>
                <div class="small-12 medium-6 columns">
                    <label for="support_email">
                        Contact email address for inactive domains<br/>
                        <input id="support_email" type="text" name="support_email" value="support@example.com" class="required" />
                    </label>
                </div>
                <div class="small-12 columns">
                    <label for="v6_support">
                        <input id="v6_support" type="checkbox" name="v6_support" />Enable IPv6 support
                    </label>
                </div>
                <div class="small-12 columns">
                    <label for="trusted_hosts">
                        Trusted Hosts (Comma delimited list of IPv4 addresses)<br/>
                        <input id="trusted_hosts" type="text" name="trusted_hosts" value="127.0.0.1" class="required"/>
                    </label>
                </div>
                <div class="small-12 columns">
                    <label for="get_data_trusted_only">
                        <input id="get_data_trusted_only" type="checkbox" name="get_data_trusted_only" />Allow access to get_data without authentication?
                    </label>
                </div>
                <div class="small-12 columns">
                    <label for="tinydns_ip">
                        IP Address of the local tinydns instance.  This is the IP that will be used for dns lookups on authoritative information<br/>
                        <input id="tinydns_ip" type="text" name="tinydns_ip" value="127.0.0.1" class="required" />
                    </label>
                </div>
                <div class="small-12 medium-6 columns">
                    <label for="records_per_page">
                        Records per page<br/>
                        <input id="records_per_page" type="text" name="records_per_page" value="75" class="required" />
                    </label>
                </div>
                <div class="small-12 medium-6 columns">
                    <label for="timeout">
                        Session Timeout (Defaults to 3600 (1 hour))<br/>
                        <input id="timeout" type="text" name="timeout" value="3600" />
                    </label>
                </div>
                <div class="small-12 columns">
                    <label for="dns_tools_dir">
                        Directory containing dnsq and dnsqr<br/>
                        <input id="dns_tools_dir" type="text" name="dns_tools_dir" value="/usr/local/bin" class="required" />
                    </label>
                </div>
                <div class="small-12 columns">
                    <label for="mysql_sessions">
                        <input id="mysql_sessions" type="checkbox" name="mysql_sessions" />Use MysQL sessions rather than files? (required when load balancing VegaDNS)
                    </label>
                </div>
            </div>
            <input type="submit" value="Save" class="button expanded">
        </div>
    </div>
</form>
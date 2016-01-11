<div class="container-fluid">
	<div class="row">
		<div class="col-md-9">
			<h2 class="page-header" id="js-overview">Overview</h2>
			<div class="help-block">
				The SMTA Simple Mailer is used to manage a server running PowerMTA and be able to submit drops to it programmatically through a set of
				APIs that are documented below.  Each API runs using the REST framework.  REST requests use the standard HTTP verbs mapped to various 
				operations.  The mapping is shown below:
			</div>
			<p />
			<h2 class="page-header" id="js-rest-implementation">REST Implementation</h2>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>HTTP Verb</th>
						<th>REST Action</th>
						<th>Additional Notes</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>GET</code></td>
						<td><code>Query</code></td>
						<td>
							Used to retrieve a record from the database.  Pass in an <code>_id</code> to retrieve a single record.  Omitting the <code>_id</code> will result in 
							a resultset of multiple records.
						</td>
					</tr>
					<tr>
						<td><code>POST</code></td>
						<td><code>Insert</code></td>
						<td>
							Used to save a record to the database.  Passing in an <code>_id</code> will normally result in an update, although it may create a new record, so check
							the documentation.
						</td>
					</tr>
					<tr>
						<td><code>PUT</code></td>
						<td><code>Update</code></td>
						<td>
							Used to update an existing record to the database.  You must pass in an <code>_id</code> otherwise an error will occur.
						</td>
					</tr>
					<tr>
						<td><code>DELETE</code></td>
						<td><code>Delete</code></td>
						<td>
							Used to delete an existing record to the database.  You must pass in an <code>_id</code> otherwise an error will occur.
						</td>
					</tr>
				</tbody>
			</table>
			<h2 class="page-header" id="js-json-responses">JSON Responses</h2>
			<div class="help-block">
				When a request is made through the API, a response is generated in <code>JSON</code> format.  Results can be parsed using any JSON library.  The main parts of a
				response are detailed below:
			</div>
			<p />
			<pre>{"result":"SUCCESS","errors":["404: The page cannot be found"],"meta":{"insert_id":0,"rows_affected":0},"pagination":{"draw":0,"page":1,"items_per_page":20,"page_count":0,"total_rows":0},"record":{},"entries":[]}</pre>
			<p />
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>JSON</th>
						<th>Additional Notes</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>RESULT</code></td>
						<td>
							Denotes whether the request was successful or failed.  A response code of <code>SUCCESS</code> or <code>FAILED</code> will be returned.
						</td>
					</tr>
					<tr>
						<td><code>ERRORS</code></td>
						<td>
							Contains an array of any errors or exceptions that occurred during the processing of your request.
						</td>
					</tr>
					<tr>
						<td><code>META</code></td>
						<td>
							Additional information such as modified counts and last inserted ids will show in this section.
						</td>
					</tr>
					<tr>
						<td><code>PAGINATION</code></td>
						<td>
							Pagination information such as the total number of rows found, which page you are viewing, and how many pages are available.
						</td>
					</tr>
					<tr>
						<td><code>RECORD</code></td>
						<td>
							When doing any operation except for <code>GET</code> with multiple results, the detailed record object will be here.  It will include 
							properties associated with it that you can traverse.
						</td>
					</tr>
					<tr>
						<td><code>ENTRIES</code></td>
						<td>
							When performing a <code>GET</code> that returns one or more results with pagination (a <code>GET</code> that was not passed an <code>_id</code> field), 
							the record objects will appear here in an array.  This array can be traversed and shown using the pagination data contained in the <code>PAGINATION</code> 
							section.
						</td>
					</tr>
				</tbody>
			</table>
			<h2 class="page-header" id="js-api-reference">API Reference</h2>
			<div class="help-block">
				The various API requests are shown below.  Each API request can be called using the main url.  No token is required when performing an API request.
			</div>
			<h2 class="page-header" id="api-daemon">Daemons</h2>
			<div class="help-block">
				Daemons are used to start and stop background processes and they run in the background constantly.  It is best to manage daemons from within the user interface.
			</div>
			<p />
			<pre>/api/daemon</pre>
			<p />
			<h3 id="api-daemon-params">Parameters</h3>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Parameter</th>
						<th>Additional Notes</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>name</code></td>
						<td>
							String (255 characters max)
						</td>
					</tr>
					<tr>
						<td><code>description</code></td>
						<td>
							String (255 characters max)
						</td>
					</tr>
					<tr>
						<td><code>class_name</code></td>
						<td>
							PHP Class name to use for this Daemon.  List is shown within the user interface.
						</td>
					</tr>
					<tr>
						<td><code>type</code></td>
						<td>
							Unique name to assign to this Daemon.  Usually this matches the last name in the class_name.
						</td>
					</tr>
					<tr>
						<td><code>threads</code></td>
						<td>
							The maximum number of threads that should be used.  1 thread will be used as a primary, monitoring thread, so this value should always be greater than 1 (2, 3, 4...)
						</td>
					</tr>
				</tbody>
			</table>
			<h3 id="api-daemon-rest">Rest Examples</h3>
			<h4>Post</h4>
			<pre>/api/daemon?name=Drop&amp;description=Drop+Daemon+Used+To+Control+Drops&amp;class_name=\Smta\Daemon\Drop&amp;type=Drop&amp;threads=3</pre>
			<h4>Get</h4>
			<pre>/api/daemon?_id=56907938d9b868630bc325e1</pre>
			<h4>Put</h4>
			<pre>/api/daemon?_id=56907938d9b868630bc325e1&amp;description=New+Description&amp;threads=10</pre>
			<h4>Delete</h4>
			<pre>/api/daemon?_id=56907938d9b868630bc325e1</pre>
			<h3 id="api-daemon-response">Example Response</h3>
			<pre>{"result":"SUCCESS","errors":[],"meta":{"insert_id":0,"rows_affected":0},"pagination":{"draw":0,"page":1,"items_per_page":20,"page_count":0,"total_rows":0},"record":{"is_running":true,"name":"Drop","description":"Drop runner takes care of starting and stopping drops","type":"Drop","class_name":"\\Smta\\Daemon\\Drop","threads":3,"pid":13450,"start_time":{"sec":1452500261,"usec":658000},"status":1,"run_status":1,"children":[],"pending_records":0,"pending_record_last_update":{"sec":1452500261,"usec":659000},"records_per_minute":6,"_id":"56907938d9b868630bc325e1"}}</pre>
			<h3 id="api-daemon-props">Response Properties</h3>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Parameter</th>
						<th>Type</th>
						<th>Additional Notes</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>name</code></td>
						<td>String</td>
						<td>
							Name of the daemon
						</td>
					</tr>
					<tr>
						<td><code>description</code></td>
						<td>String</td>
						<td>
							Description of the daemon
						</td>
					</tr>
					<tr>
						<td><code>class_name</code></td>
						<td>String</td>
						<td>
							PHP Class name to use for this Daemon.  List is shown within the user interface.
						</td>
					</tr>
					<tr>
						<td><code>type</code></td>
						<td>String</td>
						<td>
							Unique name to assign to this Daemon.  Usually this matches the last name in the class_name.
						</td>
					</tr>
					<tr>
						<td><code>threads</code></td>
						<td>Integer</td>
						<td>
							The maximum number of threads that should be used.  1 thread will be used as a primary, monitoring thread, so this value should always be greater than 1 (2, 3, 4...)
						</td>
					</tr>
					<tr>
						<td><code>is_running</code></td>
						<td>Boolean</td>
						<td>
							Returns whether the daemon is running (true/false)
						</td>
					</tr>
					<tr>
						<td><code>pid</code></td>
						<td>Integer</td>
						<td>
							Internal Process ID of the running parent process
						</td>
					</tr>
					<tr>
						<td><code>start_time</code></td>
						<td>Date Object</td>
						<td>
							Last time the process was started/checked.  Use the <code>sec</code> property as the unix time to display the date/time.
						</td>
					</tr>
					<tr>
						<td><code>pending_records</code></td>
						<td>Integer</td>
						<td>
							Total # of records in the queue that need to be processed
						</td>
					</tr>
					<tr>
						<td><code>pending_record_start_time</code></td>
						<td>Date Object</td>
						<td>
							Last time the total # of records were calculated.  Useful for calculating how many records are processed per minute.  Use the <code>sec</code> property as the unix time to display the date/time.
						</td>
					</tr>
					<tr>
						<td><code>records_per_minute</code></td>
						<td>Integer</td>
						<td>
							Average # of records processed per minute
						</td>
					</tr>
					<tr>
						<td><code>status</code></td>
						<td>Integer</td>
						<td>
							Whether the daemon is active (1) or inactive (0)
						</td>
					</tr>
					<tr>
						<td><code>run_status</code></td>
						<td>Integer</td>
						<td>
							Whether the daemon is actively running (1) or not (0)
						</td>
					</tr>
					<tr>
						<td><code>children</code></td>
						<td>Array</td>
						<td>
							Array of child processes that are currently running.  This changes frequently as child processes are spawned and killed.
						</td>
					</tr>
				</tbody>
			</table>
			<h2 class="page-header" id="api-data-field">Data Field</h2>
			<div class="help-block">
				Data Fields are used to store mapping values.  You can create any number of data fields that you need.  Each data field can be tied to one or more column names to account for
				variations in naming schemes (i.e. firstname, fname, first_name, etc)
			</div>
			<p />
			<pre>/api/data-field</pre>
			<p />
			<h3 id="api-data-field-params">Parameters</h3>
			<p />
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Parameter</th>
						<th>Additional Notes</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>name</code></td>
						<td>
							String (255 characters max)
						</td>
					</tr>
					<tr>
						<td><code>description</code></td>
						<td>
							String (255 characters max)
						</td>
					</tr>
					<tr>
						<td><code>request_fields</code></td>
						<td>
							Comma separated list of fields that can be used to match this data field (i.e. fname, firstname, first_name)
						</td>
					</tr>
					<tr>
						<td><code>key</code></td>
						<td>
							Unique name that will be used in mappings.  This name can be enclosed in hash marks (i.e. #fname#)
						</td>
					</tr>
					<tr>
						<td><code>tags</code></td>
						<td>
							Comma separated list of tags assigned to this data field that can be used for searching and grouping data fields (optional)
						</td>
					</tr>
					<tr>
						<td><code>is_common_field</code></td>
						<td>
							Used to flag this data field as a common field that will show at the top of the list in the user interface (1 = yes, 0 = no)
						</td>
					</tr>
					<tr>
						<td><code>custom_code</code></td>
						<td>
							A data field can have custom PHP code run when it is processed.  You can use this to alter the initial value before it is processed by the drop.  The field value
							and line will be passed to this custom function.  An example of a simple function is shown below.
						</td>
					</tr>
				</tbody>
			</table>
			<h3 id="api-data-field-rest">Rest Examples</h3>
			<h4>Post</h4>
			<pre>/api/data-field?name=Email&amp;description=Email+Column&amp;request_fields=email,em,email_address&amp;key=#email#&amp;tags=Common+Fields&amp;is_common_field=1</pre>
			<h4>Get</h4>
			<pre>/api/data-field?_id=56907938d9b868630bc325e1</pre>
			<h4>Put</h4>
			<pre>/api/data-field?_id=56907938d9b868630bc325e1&amp;description=New+Description&amp;key=#email#</pre>
			<h4>Delete</h4>
			<pre>/api/data-field?_id=56907938d9b868630bc325e1</pre>
			<h3 id="api-data-field-response">Example Response</h3>
			<pre>{"result":"SUCCESS","errors":[],"meta":{"insert_id":0,"rows_affected":0},"pagination":{"draw":0,"page":1,"items_per_page":20,"page_count":0,"total_rows":0},"record":{"name":"Email","description":"Email Field","key":"#email#","field_name":null,"custom_code":null,"tags":["Common Fields"],"is_system_field":false,"request_fields":["email","em","email_address"],"is_common_field":true,"_id":"56907025d9b8685c0bc325e0"}}</pre>
			<h3 id="api-data-field-props">Response Properties</h3>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Parameter</th>
						<th>Type</th>
						<th>Additional Notes</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>name</code></td>
						<td>String</td>
						<td>
							Name of this data field
						</td>
					</tr>
					<tr>
						<td><code>description</code></td>
						<td>String</td>
						<td>
							Description of this data field
						</td>
					</tr>
					<tr>
						<td><code>request_fields</code></td>
						<td>Array</td>
						<td>
							Array of fields that can be used to match this data field (i.e. fname, firstname, first_name)
						</td>
					</tr>
					<tr>
						<td><code>key</code></td>
						<td>String</td>
						<td>
							Unique name that will be used in mappings.  This name can be enclosed in hash marks (i.e. #fname#)
						</td>
					</tr>
					<tr>
						<td><code>tags</code></td>
						<td>Array</td>
						<td>
							Array of tags assigned to this data field that can be used for searching and grouping data fields (optional)
						</td>
					</tr>
					<tr>
						<td><code>is_common_field</code></td>
						<td>Boolean</td>
						<td>
							Used to flag this data field as a common field that will show at the top of the list in the user interface (1 = yes, 0 = no)
						</td>
					</tr>
					<tr>
						<td><code>is_system_field</code></td>
						<td>Boolean</td>
						<td>
							Used to flag this data field as a system generated field that should not be altered.
						</td>
					</tr>
					<tr>
						<td><code>field_name</code></td>
						<td>String</td>
						<td>
							<i>-- Not used --</i>
						</td>
					</tr>
					<tr>
						<td><code>custom_code</code></td>
						<td>String</td>
						<td>
							A data field can have custom PHP code run when it is processed.  You can use this to alter the initial value before it is processed by the drop.  The field value
							and line will be passed to this custom function.  An example of a simple function is shown below.
						</td>
					</tr>
				</tbody>
			</table>
			<h3 id="api-data-field-examples-uppercase">Converting a value to uppercase</h3>
			<pre><code class="language-html" data-lang="html"><span class="text-success">/**
 * Custom mapping function
 * $value - Value from mapping
 * $line - Array of line entries
 */</span>
$mapping_func = <span class="text-info">function ($value, $line) {</span>
	<span class="text-success">// You only need to use the following line</span>
	<span class="text-danger">return strtoupper($value);</span>
<span class="text-info">}</span>
</code></pre>
			<h3 id="api-data-field-examples-email">Stripping a user's name from the email</h3>
			<pre><code class="language-html" data-lang="html"><span class="text-success">/**
 * Custom mapping function
 * $value - Value from mapping
 * $line - Array of line entries
 */</span>
$mapping_func = <span class="text-info">function ($value, $line) {</span>
	<span class="text-success">// You only need to use the following lines</span>
	<span class="text-danger">if (strpos($value, '@') !== false) {
		return substr($value, 0, strpos($value, '@'));
	} else {
		return $value;
	}</span>
<span class="text-info">}</span>
</code></pre>
			<h2 class="page-header" id="api-domain-group">Domain Groups</h2>
			<div class="help-block">
				Domain Groups help you group different domains into similar groups.  For instance, Microsoft owns various domain names such 
				as @outlook.com, @hotmail.com, @live.com, and @microsoft.com.
			</div>
			<p />
			<pre>/api/domain-group</pre>
			<p />
			<h3 id="api-domain-group-params">Parameters</h3>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Parameter</th>
						<th>Additional Notes</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>name</code></td>
						<td>
							String (255 characters max)
						</td>
					</tr>
					<tr>
						<td><code>description</code></td>
						<td>
							String (255 characters max)
						</td>
					</tr>
					<tr>
						<td><code>domains</code></td>
						<td>
							Comma separated list of domains included in this domain group (i.e. hotmail.com, live.com, outlook.com)
						</td>
					</tr>
					<tr>
						<td><code>is_gi_default</code></td>
						<td>
							Flags this domain group to be used for all <i>unassigned</i> domains.  No domains need to be entered for GI domains.
						</td>
					</tr>
					<tr>
						<td><code>use_global_suffixes</code></td>
						<td>
							Tells the domain group that the list of domains <i>do not</i> have suffixes (.com, .net, etc) and that the suffixes will
							come from the global list in the settings.  This is useful when you have a list of country suffixes that you want to apply.
							<p />
							For instance, you could enter a domain value of <code>yahoo</code> and apply global suffixes to it like <code>.com</code>, <code>.co.uk</code>,
							<code>.de</code>, <code>.fr</code>, <code>.ca</code>, <code>.br</code>, and <code>.au</code>.
						</td>
					</tr>
				</tbody>
			</table>
			<h3 id="api-domain-group-rest">Rest Examples</h3>
			<h4>Post</h4>
			<pre>/api/domain-group?name=Hotmail&amp;description=Hotmail+domains&amp;domains=hotmail.com,live.com,outlook.com&amp;is_gi_default=0&amp;use_global_suffixes=0</pre>
			<h4>Get</h4>
			<pre>/api/domain-group?_id=56907938d9b868630bc325e1</pre>
			<h4>Put</h4>
			<pre>/api/domain-group?_id=56907938d9b868630bc325e1&amp;description=New+Description&amp;domains=hotmail.com,live.com,outlook.com,microsoft.com</pre>
			<h4>Delete</h4>
			<pre>/api/domain-group?_id=56907938d9b868630bc325e1</pre>
			<h3 id="api-domain-group-response">Example Response</h3>
			<pre>{"result":"SUCCESS","errors":[],"meta":{"insert_id":0,"rows_affected":0},"pagination":{"draw":0,"page":1,"items_per_page":20,"page_count":0,"total_rows":0},"record":{"name":"Hotmail","description":"Hotmail Domain Group","domains":["hotmail.com","outlook.com","live.com","microsoft.com"],"is_gi_default":0,"color":"#f57f12","email_count":0,"use_global_suffixes":false,"_id":"5690780ed9b8685e0bc325e0"}}</pre>
			<h3 id="api-domain-group-props">Response Properties</h3>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Parameter</th>
						<th>Type</th>
						<th>Additional Notes</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>name</code></td>
						<td>String</td>
						<td>
							Name of the domain group
						</td>
					</tr>
					<tr>
						<td><code>description</code></td>
						<td>String</td>
						<td>
							Description of the domain group
						</td>
					</tr>
					<tr>
						<td><code>domains</code></td>
						<td>Array</td>
						<td>
							Comma separated list of domains included in this domain group (i.e. hotmail.com, live.com, outlook.com)
						</td>
					</tr>
					<tr>
						<td><code>is_gi_default</code></td>
						<td>Boolean</td>
						<td>
							Flags this domain group to be used for all <i>unassigned</i> domains.  No domains need to be entered for GI domains.
						</td>
					</tr>
					<tr>
						<td><code>use_global_suffixes</code></td>
						<td>Boolean</td>
						<td>
							Tells the domain group that the list of domains <i>do not</i> have suffixes (.com, .net, etc) and that the suffixes will
							come from the global list in the settings.  This is useful when you have a list of country suffixes that you want to apply.
							<p />
							For instance, you could enter a domain value of <code>yahoo</code> and apply global suffixes to it like <code>.com</code>, <code>.co.uk</code>,
							<code>.de</code>, <code>.fr</code>, <code>.ca</code>, <code>.br</code>, and <code>.au</code>.
						</td>
					</tr>
					<tr>
						<td><code>color</code></td>
						<td>String</td>
						<td>
							Color to use when displaying the domain group in graphs
						</td>
					</tr>
					<tr>
						<td><code>email_count</code></td>
						<td>Integer</td>
						<td>
							# of emails in the domain group based on list counts
						</td>
					</tr>
				</tbody>
			</table>
			<h2 class="page-header" id="api-drop">Drop</h2>
			<div class="help-block">
				A drop is used to link a mailing list to an email template and queue it into PowerMTA.  A drop needs to be started once it is saved.  
			</div>
			<p />
			<pre>/api/drop</pre>
			<p />
			<h3 id="api-drop-params">Parameters</h3>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Parameter</th>
						<th>Additional Notes</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>name</code></td>
						<td>
							String (255 characters max)
						</td>
					</tr>
					<tr>
						<td><code>description</code></td>
						<td>
							String (255 characters max)
						</td>
					</tr>
					<tr>
						<td><code>upload_file_type</code></td>
						<td>
							Specifies whether the list file will be uploaded or exists on the server in an FTP location.<br />
							<?php echo \Smta\Drop::UPLOAD_FILE_TYPE_UPLOAD ?> = File will be uploaded in the request using the <code>list_file_location</code> parameter.<br />
							<?php echo \Smta\Drop::UPLOAD_FILE_TYPE_FTP ?> = File already exists on the server in the location specified in the <code>list_file_location</code> parameter.<br />
						</td>
					</tr>
					<tr>
						<td><code>list_file_location</code></td>
						<td>
							Either a FTP file path or the name of the uploaded file based on the <code>upload_file_type</code> parameter.
						</td>
					</tr>
					<tr>
						<td><code>delimiter</code></td>
						<td>
							Delimiter character used to parse the file.<br />
							<?php echo \Smta\Drop::DELIMITER_TAB ?> - Use a tab <code>\t</code> to parse the file<br />
							<?php echo \Smta\Drop::DELIMITER_COMMA ?> - Use a comma <code>,</code> to parse the file<br />
							<?php echo \Smta\Drop::DELIMITER_PIPE ?> - Use a pipe <code>|</code> to parse the file<br />
							<?php echo \Smta\Drop::DELIMITER_SEMICOLON ?> - Use a semicolon <code>;</code> to parse the file<br />
						</td>
					</tr>
					<tr>
						<td><code>body_type</code></td>
						<td>
							Specifies whether the body template will be uploaded or exists on the server in an FTP location.<br />
							<?php echo \Smta\Drop::BODY_TYPE_INLINE ?> = Body will be uploaded in the <code>body</code> parameter<br />
							<?php echo \Smta\Drop::BODY_TYPE_FILENAME ?> = File already exists on the server in the location specified in the <code>body_filename</code> parameter.<br />
						</td>
					</tr>
					<tr>
						<td><code>from_domain</code></td>
						<td>
							From address to use when sending mail.  This can be any domain, but it is best to use the reverse dns domain name.
						</td>
					</tr>
					<tr>
						<td><code>mapping</code></td>
						<td>
							Column mapping used to map a data field to a colum in the file.  Each mapping will contain 2 parameters - <code>name</code> and <code>default_value</code>.<br />
							<code>mapping[0][name]</code> - Uses the key name of a data field (i.e. #email#, #fname#) to map a column<br />
							<code>mapping[0][default_value]</code> - Default value to use in case the field is blank<br />
							<p />
							<pre>mapping[0][name] = '#email#';
mapping[0][default_value] = '';
mapping[1][name] = '#fname#';
mapping[1][default_value] = '';
mapping[2][name] = '#country#';
mapping[2][default_value] = 'US';
...</pre>
						</td>
					</tr>
				</tbody>
			</table>
			<h3 id="api-drop-rest">Rest Examples</h3>
			<h4>Post</h4>
			<pre>/api/drop?name=Test+Drop&amp;description=Test+Drop&amp;from_domain=john@test.com&amp;body_type=<?php echo \Smta\Drop::BODY_TYPE_INLINE ?>&amp;body=Hello+World.++This+is+a+test.&amp;upload_file_type=<?php echo \Smta\Drop::UPLOAD_FILE_TYPE_FTP ?>&amp;list_file_location=/tmp/mylist.txt</pre>
			<h4>Get</h4>
			<pre>/api/drop?_id=56910569d9b8685e0bc325e1</pre>
			<h4>Put</h4>
			<pre>/api/drop?_id=56910569d9b8685e0bc325e1&amp;description=New+Description&amp;from_domain=info@test.com</pre>
			<h4>Delete</h4>
			<pre>/api/drop?_id=56910569d9b8685e0bc325e1</pre>
			<h3 id="api-drop-response">Example Response</h3>
			<pre>{"result":"SUCCESS","errors":[],"meta":{"insert_id":0,"rows_affected":0},"pagination":{"draw":0,"page":1,"items_per_page":20,"page_count":0,"total_rows":0},"record":{"drop_time":{"sec":1452344681,"usec":949000},"name":"Test Body Drop","description":"Test Body Drop","list_file_location":"\/home\/smtaftp\/test.txt","mapping":[{"default_value":null,"name":"#firstname#","_id":""},{"default_value":null,"name":"#lastname#","_id":""},{"default_value":null,"name":"#email#","_id":""}],"from_domain":"john@slicksales.com","report_stats":{"list_size":2,"queue_size":0,"deliverd_size":null,"bounce_size":0,"drop_start_time":{"sec":1452344681,"usec":949000},"drop_end_time":{"sec":1452344681,"usec":949000},"name":null,"_id":""},"delimiter":1,"header_array":[["Mark","Hobson","hobby6@hotmail.com"],["Marcus","Johnson","hobby.red5@gmail.com"]],"default_header_data_fields":[],"filename":"\/home\/smta\/admin\/webapp\/meta\/uploads\/lists\/list_upload_56910569d9b8685e0bc325e1","percent_complete":100,"body":null,"body_filename":"\/home\/smtaftp\/body.txt","body_type":2,"is_error":false,"error_message":null,"log_filename":"\/var\/log\/smta\/drop_runner.sh_56910569d9b8685e0bc325e1.log","is_ready_to_run":false,"is_ready_to_stop":false,"is_running":false,"is_drop_continuing":false,"force_drop_reset":true,"is_drop_finished":true,"_id":"56910569d9b8685e0bc325e1"}}</pre>
			<h3 id="api-drop-props">Response Properties</h3>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Parameter</th>
						<th>Type</th>
						<th>Additional Notes</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>name</code></td>
						<td>String</td>
						<td>
							Name of this drop
						</td>
					</tr>
					<tr>
						<td><code>description</code></td>
						<td>String</td>
						<td>
							Description of this drop
						</td>
					</tr>
					<tr>
						<td><code>upload_file_type</code></td>
						<td>Integer</td>
						<td>
							Specifies whether the list file will be uploaded or exists on the server in an FTP location.<br />
							<?php echo \Smta\Drop::UPLOAD_FILE_TYPE_UPLOAD ?> = File will be uploaded in the request using the <code>list_file_location</code> parameter.<br />
							<?php echo \Smta\Drop::UPLOAD_FILE_TYPE_FTP ?> = File already exists on the server in the location specified in the <code>list_file_location</code> parameter.<br />
						</td>
					</tr>
					<tr>
						<td><code>list_file_location</code></td>
						<td>String</td>
						<td>
							Original location of the uploaded file (either the ftp file path or the location of the uploaded file)
						</td>
					</tr>
					<tr>
						<td><code>filename</code></td>
						<td>String</td>
						<td>
							Final location of the uploaded file (either the ftp file path or the location of the uploaded file)
						</td>
					</tr>
					<tr>
						<td><code>delimiter</code></td>
						<td>Integer</td>
						<td>
							Delimiter character used to parse the file.<br />
							<?php echo \Smta\Drop::DELIMITER_TAB ?> - Use a tab <code>\t</code> to parse the file<br />
							<?php echo \Smta\Drop::DELIMITER_COMMA ?> - Use a comma <code>,</code> to parse the file<br />
							<?php echo \Smta\Drop::DELIMITER_PIPE ?> - Use a pipe <code>|</code> to parse the file<br />
							<?php echo \Smta\Drop::DELIMITER_SEMICOLON ?> - Use a semicolon <code>;</code> to parse the file<br />
						</td>
					</tr>
					<tr>
						<td><code>body_type</code></td>
						<td>Integer</td>
						<td>
							Specifies whether the body template will be uploaded or exists on the server in an FTP location.<br />
							<?php echo \Smta\Drop::BODY_TYPE_INLINE ?> = Body will be uploaded in the <code>body</code> parameter<br />
							<?php echo \Smta\Drop::BODY_TYPE_FILENAME ?> = File already exists on the server in the location specified in the <code>body_filename</code> parameter.<br />
						</td>
					</tr>
					<tr>
						<td><code>from_domain</code></td>
						<td>String</td>
						<td>
							From address to use when sending mail.  This can be any domain, but it is best to use the reverse dns domain name.
						</td>
					</tr>
					<tr>
						<td><code>mapping</code></td>
						<td>Array</td>
						<td>
							Column mapping used to map a data field to a colum in the file.  Each mapping will contain 2 parameters - <code>name</code> and <code>default_value</code>.<br />
							<code>mapping[0][name]</code> - Uses the key name of a data field (i.e. #email#, #fname#) to map a column<br />
							<code>mapping[0][default_value]</code> - Default value to use in case the field is blank<br />
							<p />
							<pre>mapping[0][name] = '#email#';
mapping[0][default_value] = '';
mapping[1][name] = '#fname#';
mapping[1][default_value] = '';
mapping[2][name] = '#country#';
mapping[2][default_value] = 'US';
...</pre>
						</td>
					</tr>
					<tr>
						<td><code>report_stats</code></td>
						<td>Array</td>
						<td>
							Report statistics from the drop.  These are updated during the process of queueing mail.
							<ul>
								<li><b>list_size</b> - Size of the original uploaded list</li>
								<li><b>queue_size</b> - Amount of mail queued</li>
								<li><b>delivered_size</b> - Amount of mail delivered (based on the accounting logs)</li>
								<li><b>bounce_size</b> - Amount of mail bounced (based on the accounting logs)</li>
								<li><b>drop_start_time</b> - Time the drop was started.  Use the <code>sec</code> parameter as the unix timestamp</li>
								<li><b>drop_end_time</b> - Time the drop was started.  Use the <code>sec</code> parameter as the unix timestamp</li>
								<li><b>name</b> - <i>-- not used --</i></li>
							</ul>
						</td>
					</tr>
					<tr>
						<td><code>header_array</code></td>
						<td>Array</td>
						<td>
							Sample lines (up to 10) of the file that can be used as a preview when performing the mapping.
						</td>
					</tr>
					<tr>
						<td><code>default_header_data_fields</code></td>
						<td>Array</td>
						<td>
							Default headers that can be used.  <i>Currently ignored, but may be used in the future.</i>
						</td>
					</tr>
					<tr>
						<td><code>percent_complete</code></td>
						<td>Integer</td>
						<td>
							Percent of the drop that has completed.
						</td>
					</tr>
					<tr>
						<td><code>is_error</code></td>
						<td>Integer</td>
						<td>
							Whether an error was found while processing the drop
						</td>
					</tr>
					<tr>
						<td><code>error_message</code></td>
						<td>String</td>
						<td>
							Any errors that occurred while processing the drop
						</td>
					</tr>
					<tr>
						<td><code>log_filename</code></td>
						<td>String</td>
						<td>
							Location of the log file containing debug messages regarding this drop
						</td>
					</tr>
					<tr>
						<td><code>is_ready_to_run</code></td>
						<td>Boolean</td>
						<td>
							Whether this drop is flagged to be started.  If this is set, the Drop Daemon will start this drop at the next iteration.
						</td>
					</tr>
					<tr>
						<td><code>is_ready_to_stop</code></td>
						<td>Boolean</td>
						<td>
							Whether this drop is flagged to be stopped early.  If this is set, the Drop Daemon will stop this drop at the next iteration.
						</td>
					</tr>
					<tr>
						<td><code>is_running</code></td>
						<td>Boolean</td>
						<td>
							Whether this drop is currently running.
						</td>
					</tr>
					<tr>
						<td><code>is_drop_continuing</code></td>
						<td>Boolean</td>
						<td>
							Whether this drop will be started again and pick up where it left off.  This is for a future release.
						</td>
					</tr>
					<tr>
						<td><code>force_drop_reset</code></td>
						<td>Boolean</td>
						<td>
							Forces the drop to restart from the beginning when it is stopped and started again.
						</td>
					</tr>
					<tr>
						<td><code>is_drop_finished</code></td>
						<td>Boolean</td>
						<td>
							Whether the drop has finished running.
						</td>
					</tr>
				</tbody>
			</table>
			<h2 class="page-header" id="api-drop-raw-log">Drop Raw Log</h2>
			<div class="help-block">
				You can use this to view the raw drop log generated by a drop once it has started.
			</div>
			<p />
			<pre>/api/drop-raw-log</pre>
			<p />
			<h3 id="api-drop-raw-log-params">Parameters</h3>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Parameter</th>
						<th>Additional Notes</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>_id</code></td>
						<td>
							Drop Id (required)
						</td>
					</tr>
				</tbody>
			</table>
			<h3 id="api-drop-raw-log-rest">Rest Examples</h3>
			<h4>Post</h4>
			<pre>- not supported -</pre>
			<h4>Get</h4>
			<pre>/api/drop-raw-log?_id=56910569d9b8685e0bc325e1</pre>
			<h4>Put</h4>
			<pre>- not supported -</pre>
			<h4>Delete</h4>
			<pre>- not supported -</pre>
			<h3 id="api-drop-raw-log-response">Example Response</h3>
			<pre>{"result":"SUCCESS","errors":[],"meta":{"insert_id":0,"rows_affected":0},"pagination":{"draw":0,"page":1,"items_per_page":20,"page_count":0,"total_rows":0},"record":{"log_contents":"<div style=\"padding:10px;\">Drop&nbsp;#56910569d9b8685e0bc325e1&nbsp;started&nbsp;at&nbsp;01\/10\/2016&nbsp;9:17.15&nbsp;pm&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br \/>\n[&nbsp;0&nbsp;]&nbsp;Queued&nbsp;email&nbsp;hobby6@hotmail.com&nbsp;to&nbsp;56910569d9b8685e0bc325e1_crA7LU&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br \/>\nDone<br \/>\n<\/div>","drop_time":{"sec":1452344681,"usec":949000},"name":"Test Body Drop","description":"Test Body Drop","list_file_location":"\/home\/smtaftp\/test.txt","mapping":[{"default_value":null,"name":"#firstname#","_id":""},{"default_value":null,"name":"#lastname#","_id":""},{"default_value":null,"name":"#email#","_id":""}],"from_domain":"john@slicksales.com","report_stats":{"list_size":2,"queue_size":0,"deliverd_size":null,"bounce_size":0,"drop_start_time":{"sec":1452344681,"usec":949000},"drop_end_time":{"sec":1452344681,"usec":949000},"name":null,"_id":""},"delimiter":1,"header_array":[["Mark","Hobson","hobby6@hotmail.com"],["Marcus","Johnson","hobby.red5@gmail.com"]],"default_header_data_fields":[],"filename":"\/home\/smta\/admin\/webapp\/meta\/uploads\/lists\/list_upload_56910569d9b8685e0bc325e1","percent_complete":100,"body":null,"body_filename":"\/home\/smtaftp\/body.txt","body_type":2,"is_error":false,"error_message":null,"log_filename":"\/var\/log\/smta\/drop_runner.sh_56910569d9b8685e0bc325e1.log","is_ready_to_run":false,"is_ready_to_stop":false,"is_running":false,"is_drop_continuing":false,"force_drop_reset":true,"is_drop_finished":true,"_id":"56910569d9b8685e0bc325e1"}}</pre>
			<h3 id="api-drop-raw-log-props">Response Properties</h3>
			<div class="help-block">This command returns everything that the <code>/api/drop</code> command returns with the addition of the <code>log_contents</code> property.</div>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Parameter</th>
						<th>Type</th>
						<th>Additional Notes</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>log_contents</code></td>
						<td>String</td>
						<td>
							Contents of the log file that can be displayed to the user.
						</td>
					</tr>
				</tbody>
			</table>
			<h2 class="page-header" id="api-drop-start">Drop Start</h2>
			<div class="help-block">
				You can use this to start a drop.  A drop does not start automatically.  Once a drop is started, you can check it using a <code>GET</code> request to 
				the <code>/api/drop</code> endpoint.
			</div>
			<p />
			<pre>/api/drop-start</pre>
			<p />
			<h3 id="api-drop-start-params">Parameters</h3>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Parameter</th>
						<th>Additional Notes</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>_id</code></td>
						<td>
							Drop Id (required)
						</td>
					</tr>
				</tbody>
			</table>
			<h3 id="api-drop-start-rest">Rest Examples</h3>
			<h4>Post</h4>
			<pre>/api/drop-start?_id=56910569d9b8685e0bc325e1</pre>
			<h4>Get</h4>
			<pre>- not supported -</pre>
			<h4>Put</h4>
			<pre>/api/drop-start?_id=56910569d9b8685e0bc325e1</pre>
			<h4>Delete</h4>
			<pre>- not supported -</pre>
			<h3 id="api-drop-start-response">Example Response</h3>
			<pre>{"result":"SUCCESS","errors":[],"meta":{"insert_id":0,"rows_affected":0},"pagination":{"draw":0,"page":1,"items_per_page":20,"page_count":0,"total_rows":0},"record":{"drop_time":{"sec":1452344681,"usec":949000},"name":"Test Body Drop","description":"Test Body Drop","list_file_location":"\/home\/smtaftp\/test.txt","mapping":[{"default_value":null,"name":"#firstname#","_id":""},{"default_value":null,"name":"#lastname#","_id":""},{"default_value":null,"name":"#email#","_id":""}],"from_domain":"john@slicksales.com","report_stats":{"list_size":2,"queue_size":0,"deliverd_size":null,"bounce_size":0,"drop_start_time":{"sec":1452344681,"usec":949000},"drop_end_time":{"sec":1452344681,"usec":949000},"name":null,"_id":""},"delimiter":1,"header_array":[["Mark","Hobson","hobby6@hotmail.com"],["Marcus","Johnson","hobby.red5@gmail.com"]],"default_header_data_fields":[],"filename":"\/home\/smta\/admin\/webapp\/meta\/uploads\/lists\/list_upload_56910569d9b8685e0bc325e1","percent_complete":100,"body":null,"body_filename":"\/home\/smtaftp\/body.txt","body_type":2,"is_error":false,"error_message":null,"log_filename":"\/var\/log\/smta\/drop_runner.sh_56910569d9b8685e0bc325e1.log","is_ready_to_run":true,"is_ready_to_stop":false,"is_running":false,"is_drop_continuing":false,"force_drop_reset":true,"is_drop_finished":true,"_id":"56910569d9b8685e0bc325e1"}}</pre>
			<h3 id="api-drop-start-props">Response Properties</h3>
			<div class="help-block">This command returns everything that the <code>/api/drop</code> command returns with the addition that the <code>is_ready_to_run</code> will be true.</div>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Parameter</th>
						<th>Type</th>
						<th>Additional Notes</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>is_ready_to_run</code></td>
						<td>Boolean</td>
						<td>
							Set to true and signifies that this drop will be started by the drop daemon on the next iteration.
						</td>
					</tr>
				</tbody>
			</table>
			<h2 class="page-header" id="api-drop-stop">Drop Stop</h2>
			<div class="help-block">
				You can use this to stop a running drop.  Once a drop is started, you can check it using a <code>GET</code> request to 
				the <code>/api/drop</code> endpoint.
			</div>
			<p />
			<pre>/api/drop-stop</pre>
			<p />
			<h3 id="api-drop-stop-params">Parameters</h3>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Parameter</th>
						<th>Additional Notes</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>_id</code></td>
						<td>
							Drop Id (required)
						</td>
					</tr>
				</tbody>
			</table>
			<h3 id="api-drop-stop-rest">Rest Examples</h3>
			<h4>Post</h4>
			<pre>/api/drop-stop?_id=56910569d9b8685e0bc325e1</pre>
			<h4>Get</h4>
			<pre>- not supported -</pre>
			<h4>Put</h4>
			<pre>/api/drop-stop?_id=56910569d9b8685e0bc325e1</pre>
			<h4>Delete</h4>
			<pre>- not supported -</pre>
			<h3 id="api-drop-stop-response">Example Response</h3>
			<pre>{"result":"SUCCESS","errors":[],"meta":{"insert_id":0,"rows_affected":0},"pagination":{"draw":0,"page":1,"items_per_page":20,"page_count":0,"total_rows":0},"record":{"drop_time":{"sec":1452344681,"usec":949000},"name":"Test Body Drop","description":"Test Body Drop","list_file_location":"\/home\/smtaftp\/test.txt","mapping":[{"default_value":null,"name":"#firstname#","_id":""},{"default_value":null,"name":"#lastname#","_id":""},{"default_value":null,"name":"#email#","_id":""}],"from_domain":"john@slicksales.com","report_stats":{"list_size":2,"queue_size":0,"deliverd_size":null,"bounce_size":0,"drop_start_time":{"sec":1452344681,"usec":949000},"drop_end_time":{"sec":1452344681,"usec":949000},"name":null,"_id":""},"delimiter":1,"header_array":[["Mark","Hobson","hobby6@hotmail.com"],["Marcus","Johnson","hobby.red5@gmail.com"]],"default_header_data_fields":[],"filename":"\/home\/smta\/admin\/webapp\/meta\/uploads\/lists\/list_upload_56910569d9b8685e0bc325e1","percent_complete":100,"body":null,"body_filename":"\/home\/smtaftp\/body.txt","body_type":2,"is_error":false,"error_message":null,"log_filename":"\/var\/log\/smta\/drop_runner.sh_56910569d9b8685e0bc325e1.log","is_ready_to_run":true,"is_ready_to_stop":false,"is_running":false,"is_drop_continuing":false,"force_drop_reset":true,"is_drop_finished":true,"_id":"56910569d9b8685e0bc325e1"}}</pre>
			<h3 id="api-drop-stop-props">Response Properties</h3>
			<div class="help-block">This command returns everything that the <code>/api/drop</code> command returns with the addition that the <code>is_ready_to_stop</code> will be true.</div>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Parameter</th>
						<th>Type</th>
						<th>Additional Notes</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>is_ready_to_stop</code></td>
						<td>Boolean</td>
						<td>
							Set to true and signifies that this drop will be stopped by the drop daemon on the next iteration.
						</td>
					</tr>
				</tbody>
			</table>
			<h2 class="page-header" id="api-pmta-config">PMTA Config</h2>
			<div class="help-block">
				Displays or saves the various PowerMTA configuration files.  Upon saving, PowerMTA will be restarted.
			</div>
			<p />
			<pre>/api/pmta-config</pre>
			<p />
			<h3 id="api-pmta-config-params">Parameters</h3>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Parameter</th>
						<th>Additional Notes</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>main_config</code></td>
						<td>
							Main Configuration used by PowerMTA
						</td>
					</tr>
					<tr>
						<td><code>domain_config</code></td>
						<td>
							Domain Configuration used by PowerMTA to determine individual domain settings.
						</td>
					</tr>
					<tr>
						<td><code>backoff_config</code></td>
						<td>
							Backoff Configuration used by PowerMTA to determine which error messages trigger backoffs on queues.
						</td>
					</tr>
				</tbody>
			</table>
			<h3 id="api-pmta-config-rest">Rest Examples</h3>
			<h4>Post</h4>
			<pre>/api/pmta-config?main_config=&lt;...config contents...&gt;</pre>
			<h4>Get</h4>
			<pre>/api/pmta-config</pre>
			<h4>Put</h4>
			<pre>/api/pmta-config?backoff_config=&lt;...config contents...&gt;</pre>
			<h4>Delete</h4>
			<pre>- not supported -</pre>
			<h3 id="api-pmta-config-response">Example Response</h3>
			<pre>{"result":"SUCCESS","errors":[],"meta":{"insert_id":0,"rows_affected":0},"pagination":{"draw":0,"page":1,"items_per_page":20,"page_count":0,"total_rows":0},"record":{"main_config":"&lt;...main config here...&gt;","domain_config":"&lt;...domain config here...&gt;","backoff_config":"&lt;...backoff config here...&gt;","_id":""}}</pre>
			<h3 id="api-pmta-config-props">Response Properties</h3>
			<div class="help-block">This command returns everything that the <code>/api/drop</code> command returns with the addition that the <code>is_ready_to_stop</code> will be true.</div>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Parameter</th>
						<th>Type</th>
						<th>Additional Notes</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>main_config</code></td>
						<td>String</td>
						<td>
							Main Configuration used by PowerMTA
						</td>
					</tr>
					<tr>
						<td><code>domain_config</code></td>
						<td>String</td>
						<td>
							Domain Configuration used by PowerMTA to determine individual domain settings.
						</td>
					</tr>
					<tr>
						<td><code>backoff_config</code></td>
						<td>String</td>
						<td>
							Backoff Configuration used by PowerMTA to determine which error messages trigger backoffs on queues.
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col-md-3">
			<nav id="myAffix" class="bs-docs-sidebar hidden-print hidden-xs hidden-sm affix" data-spy="affix" data-offset-top="60" data-offset-bottom="200">
				<ul class="nav bs-docs-sidenav">
					<li><a href="#js-overview">Overview</a>
						<ul class="nav">
							<li><a href="#js-rest-implementation">REST Implementation</a></li>
							<li><a href="#js-json-responses">JSON Response</a></li>	
						</ul>
					</li>
					<li><a href="#api-daemon">Daemons</a>
						<ul class="nav">
							<li><a href="#api-daemon-params">Parameters</a></li>
							<li><a href="#api-daemon-rest">Rest Examples</a></li>
							<li><a href="#api-daemon-response">Sample Response</a></li>
							<li><a href="#api-daemon-props">Response Properties</a></li>
						</ul>
					</li>
					<li><a href="#api-data-field">Data Field</a>
						<ul class="nav">
							<li><a href="#api-data-field-params">Parameters</a></li>
							<li><a href="#api-data-field-rest">Rest Examples</a></li>
							<li><a href="#api-data-field-response">Sample Response</a></li>
							<li><a href="#api-data-field-props">Response Properties</a></li>
							<li><a href="#api-data-field-examples">Code Examples</a>
								<ul class="nav">
									<li><a href="#api-data-field-examples-uppercase">Converting a value to uppercase</a></li>
									<li><a href="#api-data-field-examples-email">Stripping a user's name from the email</a></li>	
								</ul>
							</li>
						</ul>
					</li>	
					<li><a href="#api-domain-group">Domain Groups</a>
						<ul class="nav">
							<li><a href="#api-domain-group-params">Parameters</a></li>
							<li><a href="#api-domain-group-rest">Rest Examples</a></li>
							<li><a href="#api-domain-group-response">Sample Response</a></li>
							<li><a href="#api-domain-group-props">Response Properties</a></li>
						</ul>
					</li>
					<li><a href="#api-drop">Drop</a>
						<ul class="nav">
							<li><a href="#api-drop-params">Parameters</a></li>
							<li><a href="#api-drop-rest">Rest Examples</a></li>
							<li><a href="#api-drop-response">Sample Response</a></li>
							<li><a href="#api-drop-props">Response Properties</a></li>
						</ul>
					</li>
					<li><a href="#api-drop-raw-log">Drop Raw Log</a>
						<ul class="nav">
							<li><a href="#api-drop-raw-log-params">Parameters</a></li>
							<li><a href="#api-drop-raw-log-rest">Rest Examples</a></li>
							<li><a href="#api-drop-raw-log-response">Sample Response</a></li>
							<li><a href="#api-drop-raw-log-props">Response Properties</a></li>
						</ul>
					</li>
					<li><a href="#api-drop-start">Drop Start</a>
						<ul class="nav">
							<li><a href="#api-drop-start-params">Parameters</a></li>
							<li><a href="#api-drop-start-rest">Rest Examples</a></li>
							<li><a href="#api-drop-start-response">Sample Response</a></li>
							<li><a href="#api-drop-start-props">Response Properties</a></li>
						</ul>
					</li>
					<li><a href="#api-drop-stop">Drop Stop</a>
						<ul class="nav">
							<li><a href="#api-drop-stop-params">Parameters</a></li>
							<li><a href="#api-drop-stop-rest">Rest Examples</a></li>
							<li><a href="#api-drop-stop-response">Sample Response</a></li>
							<li><a href="#api-drop-stop-props">Response Properties</a></li>
						</ul>
					</li>
					<li><a href="#api-pmta-config">PMTA Config</a>
						<ul class="nav">
							<li><a href="#api-pmta-config-params">Parameters</a></li>
							<li><a href="#api-pmta-config-rest">Rest Examples</a></li>
							<li><a href="#api-pmta-config-response">Sample Response</a></li>
							<li><a href="#api-pmta-config-props">Response Properties</a></li>
						</ul>
					</li>
				</ul>
			</nav>	
		</div>
	</div>
</div>
<script>
//<!--
$(document).ready(function() {
	$('body').scrollspy({ target: '#myAffix' });
});
//-->
</script>

<?php
/** @author Uchenna Chilaka <uche.chilaka@websiteinapage.net>
**/
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>GoogleOAuth2 Component for CakePHP</title>
        <link rel="stylesheet" href="style.css" />
    </head>
    <body>
        
        <ul>
            <li>
                <h1>DISCLAIMER</h1>
                This work is put on here to assist anyone working with CakePHP projects and needing a wrapper class for the Google OAuth 2.0 library. It is provided AS-IS. 
            </li>
            <li>
                <h1>PLUGIN VS. COMPONENT</h1>
                There are cases when it is best to use a plugin in CakePHP, and other cases where it works out to use a component. Depending on 
                your preferences, you could swing in either direction - this component provides functionality that some might see better fit for 
                a plugin - and there might be a plugin version in the future. For now - quick and dirty!
            </li>
          <li>
            <h1>REQUIREMENTS</h1>
              This component requires the following libraries:
              <ul>
                  <li>CakePHP - Your application should be based on the CakePHP Framework. You can download the framework code at <a href="http://cakephp.org/" title="CakePHP Website" target="_blank">http://cakephp.org/</a>. </li>
                  <li>A working knowledge of coding with CakePHP. The CakePHP documentation can be found here: <a href="http://book.cakephp.org/2.0/en/index.html" target="_blank">http://book.cakephp.org/2.0/en/index.html</a></li>
                  <li>A Google Console Project with OAuth credentials. To setup your google console project, visit <a href="http://cloud.google.com" target="_blank">http://cloud.google.com</a>.</li>
                    <li>
                        The Google_Oauth2Service class, available in the <a href="https://code.google.com/p/google-api-php-client/" target="_blank">Google API Client Library for PHP</a> library</li>
                  <li>The Google_Client class, also available in the <a href="https://code.google.com/p/google-api-php-client/" target="_blank">Google API Client Library for PHP</a> library</li>
            </ul>
            </li>
            <li>
              <h1>INSTALLATION</h1>
              <p>Copy the component to your <span class="code">/&lt;Application Root&gt;/app/Controller/Component/</span> directory, or any other directory bootstrapped to search for components.</p>
              <p>Replace the paths to both the Google_Oauth2Service and Google_Client class files with the correct path on your server - e.g. <span class='code'>/var/www/libraries/google/google-api-client/src/contrib/&lt;ClassFile&gt;</span> assuming that is where you have the Google API library installed on your server</p>
              <p>That's it! You're ready to start using the component within your CakePHP controllers.            </p>
              <li>
                <h1>USING THE COMPONENT</h1>
              </li>
              <p>Include the component name in your $components array </p>
          <section class='code'>
              public $components = array('GoogleOauth2');
              </section>
              <p>Next, use the <a href='#connect'><span class='fxn'>connect()</span></a> method to initialize the component when you are ready to attempt authentication against Google ID. See the method reference <a href='#connect'>below</a> for accepted arguments.</p>
              <h2>LOADING COMPONENTS ON THE FLY</h2>
              <p>You can load the component on the fly in your CakePHP application using the following syntax:</p>
              <p class='code'>$this-&gt;GoogleAuth = $this-&gt;Components-&gt;load('GoogleO
                auth2');</li>
              </p>
              <li>
              <h1>FUNCTIONS            </h1>
              <ul>
                <li><h3 id='connect'><span class='fxn'>connect</span>(Controller $controller, $config, $scopes, $api_mode)</h3>
                This method is most likey
                                be well used within your project. It initializes your authentication attempt againt the user's Google ID, and accepts the following arguments:
                                <ul>
                                  <li><span class='var'>controller</span>: Use the $this variable to pass your controller into the component for controller-level callbacks</li>
                                  <li>$config: This is an associative array with the following parameters:
                                    <ul>
                                      <li><span class='var'>client_id</span>: Your Google API client ID</li>
                                      <li><span class='var'>client_secret</span>: Your Google API secret</li>
                                      <li><span class='var'>redirect_url</span>: Your success redirect url once the authentication action is completed. This url MUST be included in the list of accepted callbacks in your google console</li>
                                    </ul>
                                  </li>
                                  <li><span class='var'>scopes</span>: This is an array of Google API scopes. These scopes are in url format. A typical array of scopes will look like this:   
                                  <p class='code'>      $scopes = array('https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/drive', 
        'https://www.googleapis.com/auth/drive.file');</p>
        The <em>email</em> and <em>profile</em> scopes are required for this component.
</li>
                                  <li></li>
                                  <li><span class='var'>api_mode</span>: This is a boolean variable. If set to true, the component will return an associative array with a <em>success</em> index that indicates whether the auth attempt was successful or not. This will be useful if you are authenticating via an API and would like to parse the associate array to a JSON string (for instance) instead of the auto-redirect action.</li>
                                </ul>
                </li>
                <li><h3 id='IsReady'><span class='fxn'>isReady</span>()</h3>
                This returns true or false after a <a href='#connect'><span class='fxn'>connect</span>()</a> call for a success or failure to authenticate. 
                </li>
                <li><h3 id='getAuthUrl'><span class='fxn'>getAuthUrl</span>()</h3>
                Returns the authentication Url against Google's OAuth API. Is useful if you are handling redirection manually. In this case, the <em>api_mode<em> variable of your <a href='#connect'><span class='fxn'>connect</span>()</a> function can
                come in handy to turn off the auto-redirect when authentication is needed.</li>
                <li><h3 id='getUser'><span class='fxn'>getUser</span>()</h3>
                Will return an associate array of the session user data.
                </li>
                <li><h3 id='getUserId'><span class='fxn'>getUserId</span>()</h3>
                Will return the Google User ID from the session user array.
                </li>
                <li><h3 id='cleanUser'><span class='fxn'>cleanUser</span>()</h3>
                Useful for logging out. Call in your <em>logout()</em> controller function to delete the google user data from the session.
                </li>
                <li><h3 id='getTokens'><span class='fxn'>getTokens</span>()</h3>
                Returns a JSON string of the tokens returned on successful authentication and stored in the session array.
                </li>
              </ul>
            </li>
              <li>
              <h1 id='QA'>QUESTIONS?</h1>
              Reach out @ <a href="https://twitter.com/uchechilaka" title="WebsiteInAPage on Twitter" target="_blank">https://twitter.com/websiteinapage</a>. I'll do my best to respond in a timely fasion. </li>
            
        </ul>
        	

        
        <?php
        // put your code here
        ?>
    </body>
</html>

<?php

class WP_MadeIT_Security_Maintenance
{
    private $defaultSettings = [];
    private $settings;

    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->defaultSettings = $this->settings->loadDefaultSettings();
    }

    public function setUp()
    {
        //Create user if not exist
        $user_name = 'madeit_support';
        $user_email = 'support@madeit.be';

        $user_id = username_exists($user_name);
        if (!$user_id and email_exists($user_email) == false) {
            $random_password = wp_generate_password(12);
            $user_id = wp_create_user($user_name, $random_password, $user_email);

            $user = get_user_by('id', $user_id);

            // Remove role
            $user->remove_role('subscriber');

            // Add role
            $user->add_role('administrator');

            $info = [
                'username' => $user_name,
                'password' => $random_password,
            ];

            //send info to made I.T.
            if (strlen($this->defaultSettings['maintenance']['key']) > 0) {
                $sendRequestToMadeIT = $this->sendCompletion($this->defaultSettings['maintenance']['key'], $info);
                $result = json_decode($sendRequestToMadeIT, true);
                if (isset($result['success']) && $result['success'] == true) {
                    //Ok
                } else {
                    wp_delete_user($user_id);
                }
            }
        }
    }

    private function sendCompletion($key, $info)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://www.madeit.be/wordpress-onderhoud/api/1.0/wp/website-setup/'.$key);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['info' => json_encode($info)]));

        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close($ch);

        return $server_output;
    }
}

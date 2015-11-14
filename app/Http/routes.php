<?php
Auth::loginUsingId(1);
/*Route::get('oauth/authorize', ['as' => 'oauth.authorize.get','middleware' => ['check-authorization-params'], function() {
    // display a form where the user can authorize the client to access it's data
    $authParams = Authorizer::getAuthCodeRequestParams();
    $formParams = array_except($authParams,'client');
    $formParams['client_id'] = $authParams['client']->getId();
    return view('oauth.authorization-form', ['params'=>$formParams,'client'=>$authParams['client']]);
}]);
Route::post('oauth/authorize', ['as' => 'oauth.authorize.post','middleware' => [ 'check-authorization-params'], function() {

    $params = Authorizer::getAuthCodeRequestParams();
    $params['user_id'] = Auth::user()->id;
    $redirectUri = '';

    // if the user has allowed the client to access its data, redirect back to the client with an auth code
    if (Input::get('approve') !== null) {
        $redirectUri = Authorizer::issueAuthCode('user', $params['user_id'], $params);
    }

    // if the user has denied the client to access its data, redirect back to the client with an error message
    if (Input::get('deny') !== null) {
        $redirectUri = Authorizer::authCodeRequestDeniedRedirectUri();
    }
    return Redirect::to($redirectUri);
}]);
Route::post('oauth/access_token', function() {
    return Response::json(Authorizer::issueAccessToken());
});*/
$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {
    $api->group(['namespace' => 'App\Api\Controllers'], function ($api) {
        $api->post('user/login','AuthController@authenticate');
        $api->post('user/register','AuthController@register');
        $api->group(['middleware' => 'oauth'], function ($api) {
            $api->get('user/me','AuthController@getAuthenticatedUser');
            $api->get('lessons','LessonsController@index');
            $api->get('lessons/{id}', 'LessonsController@show');
        });
    });
});



<?php

namespace App\Http\Controllers\API\Auth;

use Mail;
use App;
use App\Consts;
use App\Utils;
use App\User;
use App\Mail\LoginNewIP;
use App\Mail\LoginNewDevice;
use App\Models\UserConnectionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Utils\BearerToken;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use Laravel\Passport\Http\Controllers\HandlesOAuthErrors;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;
use Zend\Diactoros\Response as Psr7Response;
use App\Http\Services\UserService;
use Illuminate\Support\Facades\Event;

class LoginAPIController extends AccessTokenController
{
    use HandlesOAuthErrors;

    /**
     * Authorize a client to access the user's account.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface  $request
     * @return \Illuminate\Http\Response
     */
    public function issueToken(ServerRequestInterface $request)
    {
        return $this->withErrorHandling(function () use ($request) {
            $response = $this->convertResponse(
                $this->server->respondToAccessTokenRequest($request, new Psr7Response)
            );
            $this->verifyUserRole($request);

            return $response;
        });
    }

    private function verifyUserRole($request)
    {
        $params = $request->getParsedBody();
        $loginId = $params['username'];
        $user = User::where('login_name', $loginId)->first();
        if (!$user) {
            throw new OAuthServerException('The user is not found', 6, 'user_invalid');
        }
        if ($user->role === Consts::USER_ROLE_STOREMAN) {
            throw new OAuthServerException('You don not permission to access the resource', 6, 'user_not_permission');
        }
    }

    /**
     * @SWG\Post(
     *   path="/login",
     *   summary="Login",
     *   tags={"Authentication"},
     *   security={
     *   },
     *  @SWG\Parameter(
     *       name="username",
     *       in="formData",
     *       required=true,
     *       type="string"
     *   ),
     *  @SWG\Parameter(
     *       name="password",
     *       in="formData",
     *       required=true,
     *       type="string"
     *   ),
     *   @SWG\Response(response=200, description="Successful Operation"),
     *   @SWG\Response(response=422, description="Data Invalid"),
     *   @SWG\Response(response=500, description="Internal Server Error")
     * )
     */
    public function loginViaApi(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        request()->request->add([
            'grant_type' => 'password',
            'client_id' => 1,
            'client_secret' => env('CLIENT_SECRET'),
            'username' => $request->input('username'),
            'password' => $request->input('password'),
            'scope' => '*',
        ]);

        $request->headers->set('Accept', 'application/json');
        $request->headers->set('Content-Type', 'application/x-www-form-urlencoded');

        $token = Request::create('api/oauth/token', 'POST');
        return Route::dispatch($token);
    }
}

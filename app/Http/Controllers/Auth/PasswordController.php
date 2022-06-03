<?php namespace tcCore\Http\Controllers\Auth;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Password;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Traits\UserNotificationForController;
use tcCore\Mail\PasswordChanged;
use tcCore\Mail\PasswordChangedSelf;
use tcCore\User;

class PasswordController extends Controller {
    use UserNotificationForController;
	/**
	 * Send a reset link to the given user.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param Mailer $mailer
	 * @param TokenRepositoryInterface $tokens
	 * @return \Illuminate\Http\Response
	 */
	public function sendPasswordReset(Request $request, Mailer $mailer)
	{
		$this->validate($request, [
			'username' => 'required|email',
			'url' => 'required'
		]);

		$user = Password::getUser($request->only('username'));
		if ($user !== null) {
            // Once we have the reset token, we are ready to send the message out to this
            // user with a link to reset their password. We will then redirect back to
            // the current URI having nothing set in the session to indicate errors.
            $token = Password::getRepository()->create($user);

//            $url = $request->get('url', null);
            $url = sprintf('%spassword-reset/?token=%%s',config('app.base_url'));
            $urlLogin = BaseHelper::getLoginUrl();

			try {
				$mailer->send('emails.password', compact('token', 'user', 'url', 'urlLogin'), function (Message $m) use ($user, $token) {
					$m->to($user->getEmailForPasswordReset())
						->subject('Nieuw wachtwoord aangevraagd.');
				});
			} catch (\Throwable $th) {
				Bugsnag::notifyException($th);
			}

		}
        // we always sent the ok message as to not show a correct email address or not
		return Response::make("Ok", 200);
	}

	/**
	 * Reset the given user's password.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function passwordReset(Request $request)
	{
		$this->validate($request, [
			'token' => 'required',
			'username' => 'required|email',
			'password' => 'required|min:6',
		]);

		$credentials = $request->only(
			'username', 'password', 'token'
		);

		//Need to fool laravel...
		$credentials['password_confirmation'] = $request->get('password', null);

		$response = Password::reset($credentials, function ($user, $password) {
			$this->resetPassword($user, $password);
		});

		switch ($response) {
			case Password::PASSWORD_RESET:
                $this->notifyUser($credentials['username']);
				return Response::make("Ok", 200);
			default:
				return Response::make("Fail", 400);
		}
	}

	/**
	 * Reset the given user's password.
	 *
	 * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
	 * @param  string  $password
	 * @return void
	 */
	protected function resetPassword($user, $password)
	{
	    $user->password = bcrypt($password);

		$user->save();
	}

    protected function notifyUser($userName)
    {
        try {
            $user = User::where('username', $userName)->firstOrFail();
            $this->sendPasswordChangedMail($user);
        } catch (\Exception $e) {
            //silent fail
        }
    }

}

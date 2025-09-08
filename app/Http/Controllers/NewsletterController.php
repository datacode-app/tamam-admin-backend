<?php
namespace App\Http\Controllers;
use App\Models\Newsletter;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
class NewsletterController extends Controller
{
    //Save newsLetterSubscribe email
    /**
     * Save newsletter subscription email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function newsLetterSubscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $emailCount = Newsletter::where('email', $request->email)->count();
        if ($emailCount) {
            Toastr::warning(translate('messages.subscription_exist'));
            return back();
        } else {
            Newsletter::create($request->all());
            Toastr::success(translate('messages.subscription_successful'));
            return back();
        }
    }
}

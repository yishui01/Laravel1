<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            //以下方法是被剔除权限的，就是不需要权限验证的
            'except' => ['show', 'create', 'store', 'index', 'confirmEmail']
        ]);

        //只让未登录用户访问注册页面：
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    /*
     * 用户列表
     */
    public function index()
    {
        $users = User::paginate(10); //分页
        return view('users.index', compact('users'));
    }

    //注册页面
    public function create()
    {
        return view('users.create');
    }

    /**个人中心
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(User $user)
    {
        $statuses = $user->statuses()->orderBy('created_at', 'desc')
            ->paginate(30);
        return view('users.show', compact('user', 'statuses'));
    }


    /**存储数据
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        /*Auth::login($user);
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show', [$user]);*/

        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');
    }

    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'laravel1@163.com';
        $name = '弃子';
        $to = $user->email;
        $subject = "感谢注册 sample的 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }

    /**
     * 显示编辑页面表单
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(User $user)
    {
        //通过AuthServiceProvider和UserPolicy的验证器，不对，应该是授权检验器关联，
        //这里$user传递的是第二个参数，第一个参数框架会自动加载，不需要传递
        //用于验证当前登录的user和需要修改的user
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    /**进行更新操作
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(User $user, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);
        $this->authorize('update', $user);
        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $user->id);
    }

    public function destroy(User $user)
    {

        //现在的删除动作是对所有登录用户开放的，为此我们还需要对删除动作加上授权策略，只允许已登录的 管理员 进行删除操作。
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }

    /**
     * 激活邮箱
     * @param $token
     *
     */
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }

    public function followings(User $user)
    {
        $users = $user->followings()->paginate(30);
        $title = '关注的人';
        return view('users.show_follow', compact('users', 'title'));
    }

    public function followers(User $user)
    {
        $users = $user->followers()->paginate(30);
        $title = '粉丝';
        return view('users.show_follow', compact('users', 'title'));
    }
}

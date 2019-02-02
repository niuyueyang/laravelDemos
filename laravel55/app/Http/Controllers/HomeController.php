<?php
/**
 * Created by PhpStorm.
 * User: niuyueyang
 * Date: 2019/1/31
 * Time: 13:00
 */
namespace App\Http\Controllers;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Support\Facades\Mail;
use Session;
use Cache;

//post注意事项
//use Request，这是第一点
//在routes里面的web.php配置路由，Route::any()，uses类名@方法
//注销掉app/Kernel.php，web里面的\App\Http\Middleware\VerifyCsrfToken::class,


class HomeController extends Controller
{
    //添加
    public function add()
    {
        $name =Request::input('name','');
        $password = Request::input('pwd','');
        if(empty($name)||empty($password)){
            return response()->json(['status' => 'error','code' => 1,'message' => '用户名或者密码不能为空','data'=>array()]);
        }
        else{
            $res=DB::table('user')->insert(['name'=>$name,'pwd'=>md5($password)]);
            if($res){
                return response()->json(['status' => 'success','code' => 0,'message' => '添加成功','data'=>array('name'=>$name,'password'=>$password)]);
            }
            else{
                return response()->json(['status' => 'error','code' => 1,'message' => '添加失败','data'=>array()]);
            }
        }
    }

    //修改【如果第一次修改得内容与第二次一样，则修改失败】
    public function update(){
        $id =Request::input('id','');
        $password = Request::input('pwd','');
        if(empty($id)||empty($password)){
            return response()->json(['status' => 'error','code' => 1,'message' => 'id或者密码不能为空','data'=>array()]);
        }
        else{
            $arr=array('pwd'=>md5($password),'id'=>$id);
            $res=DB::table('user')->where(array('id'=>$id))->update($arr);
            if($res){
                return response()->json(['status' => 'success','code' => 0,'message' => '修改成功','data'=>array('password'=>$password)]);
            }
            else{
                return response()->json(['status' => 'error','code' => 1,'message' => '修改失败','data'=>array()]);
            }
        }
    }

    //查询
    public function show(){
        $id =Request::input('id','');
        if(empty($id)){
            return response()->json(['status' => 'error','code' => 1,'message' => 'id不能为空','data'=>array()]);
        }
        else{
            $res=DB::table('user')->where(array('id'=>$id))->get();
            if($res){
                return response()->json(['status' => 'success','code' => 0,'message' => '修改成功','data'=>$res]);
            }
            else{
                return response()->json(['status' => 'error','code' => 1,'message' => '修改失败','data'=>array()]);
            }
        }
    }

    //删除
    public function del(){
        $id =Request::input('id','');
        if(empty($id)){
            return response()->json(['status' => 'error','code' => 1,'message' => 'id不能为空','data'=>array()]);
        }
        else{
            $res=DB::table('user')->where(array('id'=>$id))->delete();
            if($res){
                return response()->json(['status' => 'success','code' => 0,'message' => '删除成功','data'=>array()]);
            }
            else{
                return response()->json(['status' => 'error','code' => 1,'message' => '删除失败','data'=>array()]);
            }
        }
    }

    //分页
    public function page(){
        //limit每页多少条，page第几页
        $page =Request::input('page','');
        $limit =Request::input('limit','');
        if(empty($page)){
            return response()->json(['status' => 'error','code' => 1,'message' => 'page不能为空','data'=>array()]);
        }
        else{
            $res=DB::select("select * from user limit ".$limit." offset ".($page-1)*$limit);

            return response()->json(['code'=> 200,'msg'=>'ok','data'=>$res]);
        }
    }
    function base64EncodeImage ($image_file) {
        $base64_image = '';
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        return $base64_image;
    }
    //验证码
    //composer require gregwar/captcha
    public function captcha($tmp)
    {
        //生成验证码图片的Builder对象，配置相应属性
        $builder = new CaptchaBuilder;
        //可以设置图片宽高及字体
        $builder->build($width = 250, $height = 70, $font = null);
        //获取验证码的内容
        $phrase = $builder->getPhrase();
        //把内容存入session
        Cache::set('milkcaptcha', $phrase,10);
//        Session::flash('milkcaptcha', $phrase);
        //生成图片
        header("Cache-Control: no-cache, must-revalidate");
        header('Content-Type: image/png');
        header('Access-Control-Allow-Origin: *');
        return $this->base64EncodeImage($builder->output());
    }
    //验证注册码的正确与否
    public function verifyCaptcha() {
        $userInput  =Request::input('captcha','');
        if (Cache::get('milkcaptcha') == $userInput) {
            //用户输入验证码正确
            return response()->json(['status' => 'success','code' => 0,'message' => '验证码正确','data'=>array()]);
        } else {
            //用户输入验证码错误
            return response()->json(['status' => 'error','code' => 1,'message' => '验证码错误','data'=>Cache::get('milkcaptcha')]);
        }
    }

    //邮件
    public function Mail(){
        Mail::raw('你好！',function($message)
        {
            $to = '255153187@qq.com';
            $message ->to($to)->subject('测试邮件');
        });
        if(count(Mail::failures()) < 1){
            echo '发送邮件成功，请查收！';
        }else{
            echo '发送邮件失败，请重试！';
        }
    }

    //激活邮件
    public function active(){
        $uid = 1;      //获取最新插入的id
        $activationcode = md5(time());  //获取邮箱验证时的随机串
        $data = array('email'=>'255153187@qq.com', 'name'=>'admin', 'uid'=>$uid, 'activationcode'=>$activationcode);
        Mail::send('activemail', $data, function($message){
            $email='255153187@qq.com';
            $name='admin';
            $message ->to($email,$name)->subject('欢迎注册测试账号');
        });
        if(count(Mail::failures()) < 1){
            echo '发送邮件成功，请查收！';
        }else{
            echo '发送邮件失败，请重试！';
        }
    }

    //邮件激活
    public function mailBox(){
        var_dump(Request::route('activationcode'));
    }

}
<?php

namespace App\Http\Controllers\Front;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
//Models
use App\Models\Category;
use App\Models\Article;
use App\Models\Page;
use App\Models\Contact;
use App\Models\Config;




class HomepageController extends Controller
{
    public function __construct()
    {
        if (Config::find(1)->active==0){
            return redirect()->to('site-bakimda')->send();
        }
        view()->share('pages',Page::orderBy('order','ASC')->get());
        view()->share('categories',Category::inRandomOrder()->get());
    }


    public  function index(){
        $data['articles']=Article::where('status',1)->orderBy('created_at','DESC')->paginate(5);
        $data['articles']->withPath(url('sayfa'));


        return view('front.homepage', $data);
    }
    public function single($category,$slug){
        Category::whereSlug($category)->first() ?? abort(403, 'böyle kategori yok');
        $article=Article::whereSlug($slug)->first() ?? abort(403,'error');
        $article->increment('hit');
        $data['article']=$article;


        return view('front.single',$data);

    }
    public function category($slug){
        $category = Category::whereSlug($slug)->first() ?? abort(403,'Böyle Bir Kategori yok');
        $data['category']=$category;
        $data['articles']=Article::where('category_id',$category->id)->orderBy('created_at','DESC')->paginate(2);
        return view('front.category',$data);

    }
    public  function page($slug){
       $page=Page::whereSlug($slug)->first() ?? abort(403,'boyle sayfa yok kardes');
       $data['page']=$page;
       return view('front.page',$data);
    }
    public function contact(){
        return view('front.contact');
    }
    public function contactpost(Request $request){

        $rules=[
            'name'=>'required|min:5',
            'email'=>'required|email',
            'topic'=>'required',
            'message'=>'required|min:10'
        ];
        $validate=Validator::make($request->post(),$rules);

        if($validate->fails()){
            return redirect()->route('contact')->withErrors($validate)->withInput();
        }


        //Mail::send([],[], function($message) use($request){
            //$message->from('iletisim@blogsitesi.com','Blog Sitesi');
            //$message->to('emirguneyyavuzkaplan@hotmail.com');
            //$message->setBody(' Mesajı Gönderen :'.$request->name.'<br />
                    //Mesajı Gönderen Mail :'.$request->email.'<br />
                    //Mesaj Konusu : '.$request->topic.'<br />
                    //Mesaj :'.$request->message.'<br /><br />
                    //Mesaj Gönderilme Tarihi : '.now().'','text/html');
            //$message->subject($request->name. ' iletişimden mesaj gönderdi!');
        //});

         $contact = new Contact;
         $contact->name=$request->name;
         $contact->email=$request->email;
         $contact->topic=$request->topic;
       $contact->message=$request->message;
        $contact->save();
        return redirect()->route('contact')->with('success','Mesajınız bize iletildi. Teşekkür ederiz!');
    }

}

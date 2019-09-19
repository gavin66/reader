<?php


namespace App\Console\Commands;


use App\Models\Member;
use Illuminate\Console\Command;

class UserGenerateCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'user:generate {--email= :  邮箱} {--password= : 密码} {--admin : 拥有管理员权限} {--update : 更新密码}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "新增一个账户";

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $email    = $this->option( 'email' );
        $password = $this->option( 'password' );
        $admin    = $this->option( 'admin' );
        $update   = $this->option( 'update' );


        if ( $email and $password ) {
            if ( $update ) {
                // 更新操作
                $this->update( $email, $password );
            } else {
                // 新增操作
                $this->add( $email, $password, $admin );
            }
        }

        $this->error( "请输入帐号和密码" );
    }

    /**
     * 新增用户
     *
     * @param $email
     * @param $password
     * @param $admin
     */
    private function add( $email, $password, $admin )
    {
        if ( Member::where( 'email', $email )->first() ) {
            $this->error( "此用户已存在" );

            exit( 0 );
        }

        $member               = new Member;
        $member->email        = $email;
        $member->password     = encrypt( $password );
        $member->roles        = $admin ? '["admin"]' : '["member"]';
        $member->name         = $email;
        $member->avatar       = 'https://wpimg.wallstcn.com/f778738c-e4f8-4870-b634-56703b4acafe.gif';
        $member->introduction = '春天就要来了，遇见你的春天，再也没有你的春天。';
        $member->save();

        $this->info( "已成功添加用户 $email" );
        exit( 0 );
    }

    /**
     * 更新用户密码
     *
     * @param $email
     * @param $password
     */
    private function update( $email, $password )
    {
        if ( $member = Member::where( 'email', $email )->first() ) {
            $member->password = encrypt( $password );
            $member->save();

            $this->info( "已成功更新用户密码" );
        } else {
            $this->error( "用户不存在" );
        }

        exit( 0 );
    }

}

<?php


namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class KeyGenerateCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'key:generate';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "设置应用密钥";

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $key = $this->getRandomKey();
        if ( $this->option( 'show' ) ) {
            return $this->line( '<comment>' . $key . '</comment>' );
        }
        $path = base_path( '.env' );
        if ( file_exists( $path ) ) {
            file_put_contents(
                $path,
                str_replace( env( 'APP_KEY' ), $key, file_get_contents( $path ) )
            );
        }
        $this->info( "应用密钥 [$key] 已生成" );
    }

    /**
     * Generate a random key for the application.
     *
     * @return string
     */
    protected function getRandomKey()
    {
        return Str::random( 32 );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            [ 'show', null, InputOption::VALUE_NONE, '只显示密钥,不修改 .env 文件' ],
        ];
    }
}

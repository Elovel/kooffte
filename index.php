<?php declare(strict_types=1);


use danog\MadelineProto\API;
use danog\MadelineProto\Broadcast\Progress;
use danog\MadelineProto\Broadcast\Status;
use danog\MadelineProto\EventHandler\Attributes\Handler;
use danog\MadelineProto\EventHandler\Filter\FilterCommand;
use danog\MadelineProto\EventHandler\Message;
use danog\MadelineProto\EventHandler\Message\PrivateMessage;
use danog\MadelineProto\EventHandler\SimpleFilter\FromAdmin;
use danog\MadelineProto\EventHandler\SimpleFilter\Incoming;
use danog\MadelineProto\Logger;
use danog\MadelineProto\ParseMode;
use danog\MadelineProto\Settings;
use danog\MadelineProto\Settings\Database\Mysql;
use danog\MadelineProto\SimpleEventHandler;

use function Amp\Socket\SocketAddress\fromString;


if (class_exists(API::class)) {

} elseif (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} else {

    if (!file_exists('madeline.php')) {
        copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
    }
    require_once 'madeline.php';
}
class MyEventHandler extends SimpleEventHandler
{
    public const ADMIN = "hrlot"; // !!! Change this to your username !!!


    
    public function getReportPeers()
    {
        return [self::ADMIN];
    }
    /**
     * Initialization logic.
     */
    public function onStart(): void
    {
        $this->logger("The bot was started!");

        $this->sendMessageToAdmins("The bot was started!");
    }

    #[Handler]
    public function handleMessage(Incoming&PrivateMessage $message): void
    {

        if ($message->media) {
            $message->addReaction('👌');
            $message->reply("✅️ لینک دانلود ساخته شد:\n\n".$message->media->getDownloadLink()."\n⚠️ لینک دانلود فیلتر نیست");
        }
    }


    #[FilterCommand('broadcast')]
    public function broadcastCommand(Message & FromAdmin $message): void
    {
        // We can broadcast messages to all users with /broadcast
        if (!$message->replyToMsgId) {
            $message->reply("You should reply to the message you want to broadcast.");
            return;
        }
        $this->broadcastForwardMessages(
            from_peer: $message->senderId,
            message_ids: [$message->replyToMsgId],
            drop_author: true,
            pin: true,
        );
    }

    private int $lastLog = 0;

    #[Handler]
    public function handleBroadcastProgress(Progress $progress): void
    {
        if (time() - $this->lastLog > 5 || $progress->status === Status::FINISHED) {
            $this->lastLog = time();
            $this->sendMessageToAdmins((string) $progress);
        }
    }






    #[FilterCommand('start')]
    public function responder(Message $message): void
    {
        $x = $this->getInfo($message->senderId); 
        $name = $x['User']['first_name'];
        $message->addReaction('👌');
        $message->reply("👋Hi {$name}\n Send your media here");
        
    }

    #[FilterCommand('reboot')]
    public function reboot(Message & FromAdmin $message): void
    {
      $message->reply("ok");
      $this->restart();

    }


}

$settings = new Settings;
$settings->getLogger()->setLevel(Logger::LEVEL_ULTRA_VERBOSE);


#$settings->setDb((new Mysql)->setDatabase('feedozer_hl')->setUsername('feedozer_hl')->setPassword('feedozer_hl'));


MyEventHandler::startAndLoopBot('bot.madeline', '8065622638:AAHCzKEvDWVNecDvsg0TcFAse6gquTfN2Cw', $settings);

<?php

namespace KABBOUCHI\LoggerDiscordChannel;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Monolog\Formatter\LineFormatter;
use \Monolog\Logger;
use \Monolog\Handler\AbstractProcessingHandler;

class DiscordHandler extends AbstractProcessingHandler
{
    private $initialized = false;
    private $guzzle;

    private $name;
    private $subname;

    private $webhook;
    private $statement;
    private $roleId;

	/**
	 * MonologDiscordHandler constructor.
	 * @param $webhook
	 * @param $name
	 * @param string $subname
	 * @param int $level
	 * @param bool $bubble
	 * @param null $roleId
	 */
    public function __construct($webhook, $name, $subname = '', $level = Logger::DEBUG, $bubble = true, $roleId = null)
    {
        $this->name = $name;
        $this->subname = $subname;
        $this->guzzle = new \GuzzleHttp\Client();
		$this->webhook = $webhook;
		$this->roleId = $roleId;
        parent::__construct($level, $bubble);
    }

    /**
     * @param array $record
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function write(array $record): void
    {
        $formatter = new LineFormatter(null, null, true, true);
        $formatter->includeStacktraces();
        $content = $formatter->format($record);

        // Set up the formatted log
        $log = [
            'embeds' => [
                [
                    'title' => 'Log from ' . $this->name,
                    // Use CSS for the formatter, as it provides the most distinct colouring.
                    'description' => "```css\n" . substr($content, 0, 2030). '```',
                    'color' => 0xE74C3C,
                ],
            ],
        ];

        // Tag a role if configured for it
        if($this->roleId) $log['content'] = "<@&" . $this->roleId . ">";


        // Send it to discord
        $this->guzzle->request('POST', $this->webhook, [
            RequestOptions::JSON => $log,
        ]);
    }
}


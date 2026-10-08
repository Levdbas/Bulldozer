<?php

namespace HighGround\Bulldozer\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use HighGround\Bulldozer\AbstractBlockRenderer;
use HighGround\Bulldozer\BlockRendererV1;
use HighGround\Bulldozer\BlockRendererV2;
use Mockery;
use PHPUnit\Framework\TestCase;

trait DisabledBlockFixture
{
	public bool $disable_in_context = false;

	public array $rendered_context = [];

	public function __construct()
	{
	}

	public function add_fields(): object
	{
		return new \stdClass();
	}

	public function register_block(): void
	{
	}

	public function block_context($context): array
	{
		if ($this->disable_in_context) {
			$this->set_disabled();
		}

		$this->is_disabled();
		$this->is_disabled();

		return $context;
	}

	protected function add_block_classes()
	{
	}

	protected function get_block_wrapper_attributes(array $classes, array $extra_attributes = []): string
	{
		return '';
	}

	public function notifications(): array
	{
		return self::$notifications;
	}

	public function initialize(array $fields, array $supports, bool $preview): void
	{
		$this->fields = $fields;
		$this->attributes = ['supports' => $supports];
		$this->is_preview = $preview;
		self::$title = 'Test';
		self::$notifications = [];
	}

	public function disable_from_field()
	{
		return $this->maybe_disable_block();
	}
}

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class BlockDisabledTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		Monkey\setUp();
		Functions\when('__')->returnArg();
	}

	protected function tearDown(): void
	{
		Monkey\tearDown();
		parent::tearDown();
	}

	private function renderer(string $version): AbstractBlockRenderer
	{
		if ('v1' === $version) {
			return new class extends BlockRendererV1 {
				use DisabledBlockFixture;

				public function render()
				{
					$this->rendered_context = $this->context;
				}
			};
		}

		return new class extends BlockRendererV2 {
			use DisabledBlockFixture;
		};
	}

	public static function disabledStates(): array
	{
		return [
			'enabled frontend' => [false, false, false],
			'enabled preview' => [false, true, false],
			'disabled frontend' => [true, false, true],
			'disabled preview' => [true, true, false],
		];
	}

	/**
	 * @dataProvider disabledStates
	 */
	public function test_api_returns_frontend_state_and_notifies_once(bool $disabled, bool $preview, bool $expected): void
	{
		$renderer = $this->renderer('v2');
		$renderer->initialize([], [], $preview);
		if ($disabled) {
			$renderer->set_disabled();
		}

		self::assertSame($expected, $renderer->is_disabled());
		self::assertSame($expected, $renderer->is_disabled());
		self::assertCount($disabled ? 1 : 0, $renderer->notifications());
		if ($disabled) {
			self::assertSame('warning', $renderer->notifications()[0]['type']);
			self::assertSame('This block is disabled and thus not visible on the frontend.', $renderer->notifications()[0]['message']);
		}
	}

	public static function disableFields(): array
	{
		return [
			'no support' => [['is_disabled' => true], [], false],
			'no field' => [[], ['showDisableButton' => true], false],
			'enabled field' => [['is_disabled' => false], ['showDisableButton' => true], false],
			'disabled field' => [['is_disabled' => true], ['showDisableButton' => true], true],
		];
	}

	/**
	 * @dataProvider disableFields
	 */
	public function test_field_check_defers_notification_to_api(array $fields, array $supports, bool $expected): void
	{
		$renderer = $this->renderer('v2');
		$renderer->initialize($fields, $supports, false);

		self::assertSame($expected, $renderer->disable_from_field());
		self::assertCount(0, $renderer->notifications());
		self::assertSame($expected, $renderer->is_disabled());
		self::assertCount($expected ? 1 : 0, $renderer->notifications());
	}

	public static function renderModes(): array
	{
		return [
			'v1 field frontend' => ['v1', false, false],
			'v1 field preview' => ['v1', false, true],
			'v1 programmatic frontend' => ['v1', true, false],
			'v1 programmatic preview' => ['v1', true, true],
			'v2 field frontend' => ['v2', false, false],
			'v2 field preview' => ['v2', false, true],
			'v2 programmatic frontend' => ['v2', true, false],
			'v2 programmatic preview' => ['v2', true, true],
		];
	}

	/**
	 * @dataProvider renderModes
	 */
	public function test_compile_uses_api_and_resets_state_between_blocks(string $version, bool $programmatic, bool $preview): void
	{
		$renderer = $this->renderer($version);
		$renderer->disable_in_context = $programmatic;
		$fields = ['is_disabled' => !$programmatic];
		Functions\when('get_fields')->alias(static function () use (&$fields) {
			return $fields;
		});
		Functions\when('apply_filters')->alias(static function ($hook, $value) {
			return $value;
		});

		$timber = Mockery::mock('alias:Timber\Timber');
		$timber->shouldReceive('context')->andReturn([]);
		if ('v2' === $version) {
			$timber->shouldReceive('compile')->andReturnUsing(static function ($template, $context) use ($renderer) {
				$renderer->rendered_context = $context;
				return '';
			});
		}

		$attributes = [
			'title' => 'Test',
			'name' => 'acf/test',
			'id' => 'test-block',
			'supports' => ['showDisableButton' => true],
		];

		$renderer->compile($attributes, '', $preview);
		self::assertSame(!$preview, $renderer->rendered_context['is_disabled']);
		self::assertCount(1, $renderer->rendered_context['notifications']);

		$renderer->compile($attributes, '', $preview);
		self::assertCount(1, $renderer->rendered_context['notifications']);

		$fields = ['is_disabled' => false];
		$renderer->disable_in_context = false;
		$renderer->compile($attributes, '', $preview);
		self::assertFalse($renderer->rendered_context['is_disabled']);
		self::assertCount(0, $renderer->rendered_context['notifications']);
	}
}

<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Tests;

use PHPUnit\Framework\TestCase;

/**
 * The README and docs/ follow the authoring standard: one footer back to the index at the right
 * depth, no docs/README.md, and no relative link that points at nothing. 17 pages existed on
 * 2026-10-21; the floor makes a glob that stops matching fail.
 */
final class DocsTest extends TestCase
{
    private const MIN_PAGES = 17;

    private static function root(): string
    {
        return dirname(__DIR__);
    }

    /** @return list<string> */
    private static function pages(): array
    {
        $root = self::root();

        return array_merge(glob($root . '/docs/*.md') ?: [], glob($root . '/docs/tools/*.md') ?: [], glob($root . '/docs/recipes/*.md') ?: []);
    }

    public function test_every_page_has_the_index_footer_exactly_once_at_its_depth(): void
    {
        $pages = self::pages();

        foreach ($pages as $page) {
            $depth  = substr_count(substr($page, strlen(self::root() . '/docs/')), '/');
            $footer = '[← Docs index](' . str_repeat('../', $depth + 1) . 'README.md#documentation)';
            $text   = (string) file_get_contents($page);

            $this->assertSame(1, substr_count($text, 'Docs index]('), $page);
            $this->assertStringEndsWith($footer . "\n", $text, $page);
            $this->assertStringStartsWith('# ', $text, $page);
        }

        $this->assertGreaterThanOrEqual(self::MIN_PAGES, count($pages));
        $this->assertFileDoesNotExist(self::root() . '/docs/README.md');
    }

    public function test_relative_links_resolve(): void
    {
        $files   = array_merge([self::root() . '/README.md'], self::pages());
        $checked = 0;

        foreach ($files as $file) {
            preg_match_all('/\]\(([^)#\s]+)(#[^)]*)?\)/', (string) file_get_contents($file), $m);
            foreach ($m[1] as $target) {
                if (preg_match('#^[a-z]+://#', $target)) {
                    continue;
                }
                $this->assertFileExists(dirname($file) . '/' . $target, "{$file} links {$target}");
                $checked++;
            }
        }

        $this->assertGreaterThan(50, $checked);
    }

    /**
     * Commands in the docs must work from any clone, so no page may name a path on one
     * maintainer's machine: a home directory, or a "~/" path. The API's own "~" segments
     * (/domains/~/users/~) are preceded by a slash and are not matched.
     */
    public function test_no_document_names_a_local_machine_path(): void
    {
        $root  = self::root();
        $files = array_merge(
            array_filter([$root . '/README.md', $root . '/CHANGELOG.md', $root . '/CONTRIBUTING.md', $root . '/SECURITY.md'], 'is_file'),
            self::pages(),
        );
        $pattern = '#(/Users/|/home/[a-z]|[A-Z]:\\\\Users\\\\|(?<![/\w])~/)#';

        foreach ($files as $file) {
            foreach (file($file) ?: [] as $number => $line) {
                $this->assertDoesNotMatchRegularExpression($pattern, $line, sprintf('%s:%d names a local path', $file, $number + 1));
            }
        }

        $this->assertGreaterThanOrEqual(self::MIN_PAGES + 4, count($files));
    }

    public function test_the_readme_keeps_the_required_sections_in_order(): void
    {
        $readme = (string) file_get_contents(self::root() . '/README.md');
        $offsets = array_map(static fn (string $h): int|false => strpos($readme, $h), [
            '# simtabi/netsapiens-api', '## Install', '## Quick start guide and usage', '### Getting started', '### Usage',
            '## <a name="documentation"></a>Documentation', '## License',
        ]);

        $this->assertNotContains(false, $offsets);
        $sorted = $offsets;
        sort($sorted);
        $this->assertSame($sorted, $offsets);
    }
}

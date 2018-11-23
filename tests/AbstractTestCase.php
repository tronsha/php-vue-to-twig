<?php declare(strict_types=1);

namespace Macavity\VueToTwig\Tests;

use DirectoryIterator;
use DOMDocument;

abstract class AbstractTestCase extends \PHPUnit\Framework\TestCase
{
    protected function assertEqualHtml($expectedResult, $result): void
    {
        $expectedResult = $this->normalizeHtml($expectedResult);
        $result = $this->normalizeHtml($result);

        $this->assertEquals($expectedResult, $result);
    }

    protected function createDocumentWithHtml(string $html): DOMDocument
    {
        $vueDocument = new DOMDocument();
        @$vueDocument->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        return $vueDocument;
    }

    protected function normalizeHtml($html): string
    {
        $html = preg_replace('/<!--.*?-->/', '', $html);
        $html = preg_replace('/\s+/', ' ', $html);

        // Trim node text
        $html = str_replace('> ', ">", $html);
        $html = str_replace(' <', "<", $html);

        // Each tag (open and close) on a new line
        $html = str_replace('>', ">\n", $html);
        $html = str_replace('<', "\n<", $html);

        // Remove duplicated new lines
        $html = str_replace("\n\n", "\n", $html);

        return trim($html);
    }

    protected function loadFixturesFromDir(string $dir): array
    {
        $fixtureDir = __DIR__ . '/fixtures/' . $dir;

        $cases = [];

        foreach (new DirectoryIterator($fixtureDir) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->getExtension() !== 'vue') {
                continue;
            }

            // Skip files which have an "x" prefix
            if (substr($fileInfo->getBasename(), 0, 1) === 'x') {
                continue;
            }

            $templateFile = $fileInfo->getPathname();
            $twigFile = str_replace('.vue', '.twig', $templateFile);

            $template = file_get_contents($templateFile);
            $expected = file_get_contents($twigFile);

            $cases[$fileInfo->getBasename('.vue')] = [
                $template,
                $expected,
            ];
        }

        return $cases;
    }
}
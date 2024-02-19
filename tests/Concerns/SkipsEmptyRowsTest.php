<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

class SkipsEmptyRowsTest extends TestCase
{
    /**
     *
     */
    public function test_skips_empty_rows_when_importing_to_collection()
    {
        $import = new class implements ToCollection, SkipsEmptyRows
        {
            use Importable;

            public $called = false;

            /**
             * @param  Collection  $collection
             */
            public function collection(Collection $collection)
            {
                $this->called = true;

                Assert::assertEquals([
                    ['Test1', 'Test2'],
                    ['Test3', 'Test4'],
                    ['Test5', 'Test6'],
                ], $collection->toArray());
            }
        };

        $import->import('import-empty-rows.xlsx');

        $this->assertTrue($import->called);
    }

    /**
     *
     */
    public function test_skips_empty_rows_when_importing_on_each_row()
    {
        $import = new class implements OnEachRow, SkipsEmptyRows
        {
            use Importable;

            public $rows = 0;

            /**
             * @param  Row  $row
             */
            public function onRow(Row $row)
            {
                Assert::assertFalse($row->isEmpty());

                $this->rows++;
            }
        };

        $import->import('import-empty-rows.xlsx');

        $this->assertEquals(3, $import->rows);
    }

    /**
     *
     */
    public function test_skips_empty_rows_when_importing_to_model()
    {
        $import = new class implements ToModel, SkipsEmptyRows
        {
            use Importable;

            public $rows = 0;

            /**
             * @param  array  $row
             * @return Model|Model[]|null
             */
            public function model(array $row)
            {
                $this->rows++;

                return null;
            }
        };

        $import->import('import-empty-rows.xlsx');

        $this->assertEquals(3, $import->rows);
    }

    /**
     *
     */
    public function test_custom_skips_rows_when_importing_to_collection()
    {
        $import = new class implements SkipsEmptyRows, ToCollection
        {
            use Importable;

            public $called = false;

            /**
             * @param  Collection  $collection
             */
            public function collection(Collection $collection)
            {
                $this->called = true;

                Assert::assertEquals([
                    ['Test1', 'Test2'],
                    ['Test3', 'Test4'],
                ], $collection->toArray());
            }

            public function isEmptyWhen(array $row)
            {
                return $row[0] == 'Test5' && $row[1] == 'Test6';
            }
        };

        $import->import('import-empty-rows.xlsx');
        $this->assertTrue($import->called);
    }

    /**
     *
     */
    public function test_custom_skips_rows_when_importing_to_model()
    {
        $import = new class implements SkipsEmptyRows, ToModel
        {
            use Importable;

            public $called = false;

            /**
             * @param  array  $row
             */
            public function model(array $row)
            {
                Assert::assertEquals('Not empty', $row[0]);
            }

            public function isEmptyWhen(array $row): bool
            {
                $this->called = true;

                return $row[0] === 'Empty';
            }
        };

        $import->import('skip-empty-rows-with-is-empty-when.xlsx');
        $this->assertTrue($import->called);
    }

    /**
     *
     */
    public function test_custom_skips_rows_when_using_oneachrow()
    {
        $import = new class implements SkipsEmptyRows, OnEachRow
        {
            use Importable;

            public $called = false;

            /**
             * @param  array  $row
             */
            public function onRow(Row $row)
            {
                Assert::assertEquals('Not empty', $row[0]);
            }

            public function isEmptyWhen(array $row): bool
            {
                $this->called = true;

                return $row[0] === 'Empty';
            }
        };

        $import->import('skip-empty-rows-with-is-empty-when.xlsx');
        $this->assertTrue($import->called);
    }
}

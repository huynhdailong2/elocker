<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BaseExport implements WithEvents, ShouldAutoSize {

    protected $hideColumns = [];

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $parentStyle = $event->sheet->getDelegate()->getParent()->getDefaultStyle();

                $parentStyle->getFont()
                    ->setName('Arial')
                    ->setSize(10);

                $event->sheet->freezePane('A2');

                $headerStyle = $event->sheet->getStyle('A1:AW1');
                $headerStyle->getFont()->setBold(true);
                $headerStyle->getAlignment()
                    ->setWrapText(true)
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $highestRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle("A2:AW{$highestRow}")->getAlignment()
                    ->setWrapText(true)
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $this->setInvisibleColumns($event);
            },
        ];
    }

    private function setInvisibleColumns($event)
    {
        foreach ($this->hideColumns as $column) {
            $event->sheet->getColumnDimension($column)->setVisible(false);
        }
    }

}

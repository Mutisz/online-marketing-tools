<?php

namespace OMT\View;

/**
 * 
 * @author Mateusz Tokarski
 * @created Apr 12, 2016
 */
class HotelsExcelView {

	public function display($hotels) {
		$excel = new \PHPExcel();
		$sheet_index = 0;
		$excel->setActiveSheetIndex($sheet_index);
		foreach ($hotels as $hotel => $offers) {
			$sheet = $excel->getActiveSheet();
			$sheet->setTitle($this->prepareTitle($hotel));

			$row = 1;
			$this->addHeader($sheet,$row);
			$this->addBody($sheet, $row, $offers);

			$excel->createSheet();
			$excel->setActiveSheetIndex(++$sheet_index);
		}

		$excel->setActiveSheetIndex(0);

		$filename = 'hotels_comparison';
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment;filename=\"$filename.xlsx\"");
		header('Cache-Control: max-age=0');
		$writer = \PHPExcel_IOFactory::createWriter($excel, "Excel2007");
		$writer->save('php://output');
	}

	protected function addHeader(\PHPExcel_Worksheet $sheet, &$row) {
		// Add original headers
		$headers = [
			'Date', 'Duration', "Max\nadults", 'Room', 'Meal type', "Price\nper adult", "Price total"
		];
		foreach ($headers as $index => $header) {
			$sheet->mergeCellsByColumnAndRow($index, $row, $index, $row + 1);
			$sheet->setCellValueByColumnAndRow($index, $row, $header);
			$sheet->getColumnDimensionByColumn($index)
				->setAutoSize(true);
			$sheet->getStyleByColumnAndRow($index, $row)
				->getAlignment()
				->setWrapText(true);
		}

		// Add comparison headers
		$comparison_headers = [
			'Operator', 'Date', 'Room',
			"Price\nper adult", "Price\ntotal", "Price total\ndifference"
		];
		$comparison_headers_colspan = $index + count($comparison_headers);
		$comparison_headers_index = ++$index;
		$sheet->mergeCellsByColumnAndRow($comparison_headers_index, $row, $comparison_headers_colspan, $row);
		$sheet->setCellValueByColumnAndRow($comparison_headers_index, $row++, 'Comparison');
		foreach ($comparison_headers as $comparison_header) {
			$sheet->setCellValueByColumnAndRow($comparison_headers_index, $row, $comparison_header);
			$sheet->getColumnDimensionByColumn($comparison_headers_index)
				->setAutoSize(true);
			$sheet->getStyleByColumnAndRow($comparison_headers_index, $row)
				->getAlignment()
				->setWrapText(true);
			$comparison_headers_index++;
		}

		// Broaden second comparisons row
		$sheet->getRowDimension($row)->setRowHeight(30);

		$row++;
	}

	protected function addBody(\PHPExcel_Worksheet $sheet, &$row, $offers) {
		foreach ($offers as $offer) {
			$offer_data = $offer['offer'];
			$comparison = $offer['comparison'];
			$rowspan = $comparison ? $row + count($comparison) - 1 : $row;
			$sheet->mergeCellsByColumnAndRow(0, $row, 0, $rowspan);
			$sheet->setCellValueByColumnAndRow(0, $row, $offer_data['date']);
			$sheet->mergeCellsByColumnAndRow(1, $row, 1, $rowspan);
			$sheet->setCellValueByColumnAndRow(1, $row, $offer_data['duration']);
			$sheet->mergeCellsByColumnAndRow(2, $row, 2, $rowspan);
			$sheet->setCellValueByColumnAndRow(2, $row, $offer_data['adults']);
			$sheet->mergeCellsByColumnAndRow(3, $row, 3, $rowspan);
			$sheet->setCellValueByColumnAndRow(3, $row, $offer_data['room']);
			$sheet->mergeCellsByColumnAndRow(4, $row, 4, $rowspan);
			$sheet->setCellValueByColumnAndRow(4, $row, $offer_data['meal_type']);
			$sheet->mergeCellsByColumnAndRow(5, $row, 5, $rowspan);
			$sheet->setCellValueByColumnAndRow(5, $row, $offer_data['price']);
			$sheet->mergeCellsByColumnAndRow(6, $row, 6, $rowspan);
			$sheet->setCellValueByColumnAndRow(6, $row, $offer_data['total']);
			if ($comparison) {
				// Add comparison rows
				foreach ($comparison as $operator => $comparison_data) {
					$sheet->setCellValueByColumnAndRow(7, $row, $operator);
					$sheet->setCellValueByColumnAndRow(8, $row, $comparison_data['date']);
					$sheet->setCellValueByColumnAndRow(9, $row, $comparison_data['room']);
					$sheet->setCellValueByColumnAndRow(10, $row, $comparison_data['price']);
					$sheet->setCellValueByColumnAndRow(11, $row, $comparison_data['total']);
					$sheet->setCellValueByColumnAndRow(12, $row, $comparison_data['diff']);
					$row++;
				}
			} else {
				// Just move to next offer
				$row++;
			}
		}
	}

	protected function prepareTitle($title) {
		return strlen($title) > 31 ? substr($title, 0, 28) . '...' : $title;
	}

}

<?php

namespace App\Http\Controllers\Admin\Item;

use App\Contracts\Repositories\UnitRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Enums\ExportFileNames\Admin\Unit;
use App\Enums\ViewPaths\Admin\Unit as UnitViewPath;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\UnitAddRequest;
use App\Http\Requests\Admin\UnitUpdateRequest;
use App\Services\UnitService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UnitController extends BaseController
{
    public function __construct(
        protected UnitRepositoryInterface $unitRepo,
        protected UnitService $unitService,
        protected TranslationRepositoryInterface $translationRepo
    )
    {
    }

    public function index(?Request $request): View|Collection|LengthAwarePaginator|null
    {
        return $this->getListView();
    }

    private function getListView(): View
    {

        $units = $this->unitRepo->getListWhere(
            searchValue: request()?->search,
            dataLimit: config('default_pagination')
        );
        $language = getWebConfig('language');
        $defaultLang = str_replace('_', '-', app()->getLocale());
        return view(UnitViewPath::INDEX[VIEW], compact('units','language','defaultLang'));
    }

    public function add(UnitAddRequest $request): RedirectResponse
    {
        $unit = $this->unitRepo->add(data: $this->unitService->getAddData(request: $request));
        $this->translationRepo->addByModel(request: $request, model: $unit, modelPath: 'App\Models\Unit', attribute: 'unit');
        Toastr::success(translate('messages.unit_added_successfully'));
        return back();
    }

    public function getUpdateView(string|int $id): View
    {
        $unit = $this->unitRepo->getFirstWithoutGlobalScopeWhere(params: ['id' => $id]);
        $language = getWebConfig('language');
        $defaultLang = str_replace('_', '-', app()->getLocale());
        return view(UnitViewPath::UPDATE[VIEW], compact('unit','language','defaultLang'));
    }

    public function update(UnitUpdateRequest $request, $id): RedirectResponse
    {
        $unit = $this->unitRepo->update(id: $id ,data: $this->unitService->getAddData(request: $request));
        $this->translationRepo->updateByModel(request: $request, model: $unit, modelPath: 'App\Models\Unit', attribute: 'unit');
        Toastr::success(translate('messages.unit_updated_successfully'));
        return back();
    }

    public function delete(Request $request): RedirectResponse
    {
        $this->unitRepo->delete(id: $request['id']);
        Toastr::success(translate('messages.unit_deleted_successfully'));
        return back();
    }

    /**
     * @throws WriterNotOpenedException
     * @throws IOException
     * @throws UnsupportedTypeException
     * @throws InvalidArgumentException
     */
    public function exportList(string $type): StreamedResponse|string
    {
        $collection = $this->unitRepo->getList();

        if($type == 'excel'){
            return (new FastExcel($this->unitService->processExportData(collection: $collection)))->download(Unit::EXPORT_XLSX);
        }else{
            return (new FastExcel($this->unitService->processExportData(collection: $collection)))->download(Unit::EXPORT_CSV);
        }
    }

    public function search(Request $request): JsonResponse
    {
        $units = $this->unitRepo->getListWhere(
            searchValue: $request['search'],
            dataLimit: 50
        );

        return response()->json([
            'view'=>view(UnitViewPath::SEARCH[VIEW],compact('units'))->render()
        ]);
    }
}

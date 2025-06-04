<?php

namespace App\Http\Controllers\Admin\Item;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Enums\ExportFileNames\Admin\Category;
use App\Enums\ViewPaths\Admin\Category as CategoryViewPath;
use App\Exports\CategoryExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\CategoryAddRequest;
use App\Http\Requests\Admin\CategoryBulkExportRequest;
use App\Http\Requests\Admin\CategoryBulkImportRequest;
use App\Http\Requests\Admin\CategoryUpdateRequest;
use App\Services\CategoryService;
use App\Traits\ImportExportTrait;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CategoryController extends BaseController
{
    use ImportExportTrait;

    public function __construct(
        protected CategoryRepositoryInterface    $categoryRepo,
        protected CategoryService                $categoryService,
        protected TranslationRepositoryInterface $translationRepo
    )
    {
    }

    public function index(?Request $request): View|Collection|LengthAwarePaginator|null
    {
        return $this->getCategoryView($request);
    }

    private function getCategoryView(Request $request): View
    {
        $categories = $this->categoryRepo->getListWhere(
            searchValue: $request['search'],
            filters: ['position' => $request['position']],
            relations: ['module'],
            dataLimit: config('default_pagination')
        );

        $mainCategories = $this->categoryRepo->getMainList(
            filters: ['position' => 0],
            relations: ['module'],
        );

        $language = getWebConfig('language');
        $defaultLang = str_replace('_', '-', app()->getLocale());
        return view($this->categoryService->getViewByPosition($request['position']), compact('categories','language','defaultLang','mainCategories'));
    }

    public function add(CategoryAddRequest $request): RedirectResponse
    {
        $parentCategory = $this->categoryRepo->getFirstWhere(params: ['id' => $request['parent_id']]);
        $category = $this->categoryRepo->add(
            data: $this->categoryService->getAddData(
                request: $request,
                parentCategory: $parentCategory
            )
        );
        $this->translationRepo->addByModel(request: $request, model: $category, modelPath: 'App\Models\Category', attribute: 'name');
        Toastr::success( $request['position'] == 0 ?    translate('messages.category_added_successfully') : translate('messages.Sub_category_added_successfully'));
        return back();
    }

    public function getUpdateView(string|int $id): View
    {
        $category = $this->categoryRepo->getFirstWithoutGlobalScopeWhere(params: ['id' => $id]);
        $language = getWebConfig('language');
        $defaultLang = str_replace('_', '-', app()->getLocale());
        return view(CategoryViewPath::UPDATE['view'], compact('category','language','defaultLang'));
    }

    public function updateStatus(Request $request): RedirectResponse
    {
        $this->categoryRepo->update(id: $request['id'], data: ['status' => $request['status']]);
        Toastr::success(translate('messages.category_status_updated'));
        return back();
    }

    public function updateFeatured(Request $request): RedirectResponse
    {
        $this->categoryRepo->update(id: $request['id'], data: ['featured' => $request['featured']]);
        Toastr::success(translate('messages.category_featured_updated'));
        return back();
    }

    public function update(CategoryUpdateRequest $request, string|int $id): RedirectResponse
    {
        $mainCategory = $this->categoryRepo->getFirstWhere(params: ['id' => $id]);
        $category = $this->categoryRepo->update(id: $id, data: $this->categoryService->getUpdateData(request: $request, object: $mainCategory));
        $this->translationRepo->updateByModel(request: $request, model: $category, modelPath: 'App\Models\Category', attribute: 'name');
        Toastr::success( $category['position'] == 0 ?    translate('messages.category_updated_successfully') : translate('messages.Sub_category_updated_successfully'));
        return redirect()->route('admin.category.add',['position' => $mainCategory->position]);
    }

    public function delete(Request $request): RedirectResponse
    {
        if ($this->categoryRepo->delete(id: $request['id'])) {
            Toastr::success('Category removed!');
        } else {
            Toastr::warning(translate('messages.remove_sub_categories_first'));
        }
        return back();
    }

    public function getNameList(Request $request): JsonResponse
    {
        $data = $this->categoryRepo->getNameList(request: $request, dataLimit: 8);
        $data[] = (object)['id' => 'all', 'text' => 'All'];
        return response()->json($data);
    }

    public function updatePriority(Request $request): RedirectResponse
    {
        $this->categoryRepo->update(id: $request['category'], data: ['priority' => $request['priority']]);
        Toastr::success(translate('messages.category_priority_updated successfully'));
        return back();
    }

    public function getBulkImportView(): View
    {
        return view(CategoryViewPath::BULK_IMPORT['view']);
    }

    public function importBulkData(CategoryBulkImportRequest $request): RedirectResponse
    {
        $data = $this->categoryService->getImportData(request: $request);

        if (array_key_exists('flag', $data) && $data['flag'] == 'wrong_format') {
            Toastr::error(translate('messages.you_have_uploaded_a_wrong_format_file'));
            return back();
        }

        if (array_key_exists('flag', $data) && $data['flag'] == 'required_fields') {
            Toastr::error(translate('messages.please_fill_all_required_fields'));
            return back();
        }

        try {
            DB::beginTransaction();
            $this->categoryRepo->addByChunk(data: $data);
            DB::commit();
        } catch (Exception) {
            DB::rollBack();
            Toastr::error(translate('messages.failed_to_import_data'));
            return back();
        }

        Toastr::success(translate('messages.category_imported_successfully', ['count' => count($data)]));
        return back();
    }

    public function updateBulkData(CategoryBulkImportRequest $request): RedirectResponse
    {
        $data = $this->categoryService->getImportData(request: $request, toAdd: false);

        if (array_key_exists('flag', $data) && $data['flag'] == 'wrong_format') {
            Toastr::error(translate('messages.you_have_uploaded_a_wrong_format_file'));
            return back();
        }

        if (array_key_exists('flag', $data) && $data['flag'] == 'required_fields') {
            Toastr::error(translate('messages.please_fill_all_required_fields'));
            return back();
        }

        try {
            DB::beginTransaction();
            $this->categoryRepo->updateByChunk(data: $data);
            DB::commit();
        } catch (Exception) {
            DB::rollBack();
            Toastr::error(translate('messages.failed_to_import_data'));
            return back();
        }

        Toastr::success(translate('messages.category_updated_successfully', ['count' => count($data)]));
        return back();
    }

    public function getBulkExportView(): View
    {
        return view(CategoryViewPath::BULK_EXPORT['view']);
    }

    /**
     * @throws IOException
     * @throws WriterNotOpenedException
     * @throws UnsupportedTypeException
     * @throws InvalidArgumentException
     */
    public function exportBulkData(CategoryBulkExportRequest $request): StreamedResponse|string
    {
        $categories = $this->categoryRepo->getBulkExportList(request: $request);
        return (new FastExcel($this->categoryService->getExportData(collection: $this->exportGenerator(data: $categories))))->download(Category::EXPORT_XLSX);
    }

    public function exportList(Request $request): BinaryFileResponse
    {
        $categories = $this->categoryRepo->getExportList(request: $request);
        $data = [
            'data' => $categories,
            'search' => $request['search'] ?? null,
        ];

        if ($request['type'] == 'csv') {
            return Excel::download(new CategoryExport($data), Category::EXPORT_CSV);
        }
        return Excel::download(new CategoryExport($data), Category::EXPORT_XLSX);
    }
}

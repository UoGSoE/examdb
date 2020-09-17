<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Appstract\Options\Option;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OptionsEditor extends Component
{
    public $options;
    public $defaultDateOptions;
    public $wasSaved = true;

    public function mount()
    {
        $this->defaultDateOptions = config('exampapers.defaultDateOptions');
        $this->options = $this->getExistingOptionsWithFormattedDates($this->defaultDateOptions);
    }

    protected function getExistingOptionsWithFormattedDates(array $dateOptions): array
    {
        $defaultDateOptionKeys = collect($this->defaultDateOptions)->map(function ($option) {
            return $option['name'];
        });
        return Option::all()->flatMap(function ($option) use ($defaultDateOptionKeys) {
            if ($defaultDateOptionKeys->contains($option['key'])) {
                return [$option->key => Carbon::createFromFormat('Y-m-d', $option->value)->format('d/m/Y')];
            }
            return [$option->key => $option->value];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.options-editor');
    }

    public function updated($attribute)
    {
        $this->wasSaved = false;
    }

    public function save()
    {
        $this->validate([
            'options.date_receive_call_for_papers' => 'required|date_format:d/m/Y',
            'options.glasgow_staff_submission_deadline' => 'required|date_format:d/m/Y',
            'options.uestc_staff_submission_deadline' => 'required|date_format:d/m/Y',
            'options.glasgow_internal_moderation_deadline' => 'required|date_format:d/m/Y',
            'options.uestc_internal_moderation_deadline' => 'required|date_format:d/m/Y',
            'options.date_remind_glasgow_office_externals' => 'required|date_format:d/m/Y',
            'options.date_remind_uestc_office_externals' => 'required|date_format:d/m/Y',
            'options.glasgow_external_moderation_deadline' => 'required|date_format:d/m/Y',
            'options.uestc_external_moderation_deadline' => 'required|date_format:d/m/Y',
            'options.glasgow_print_ready_deadline' => 'required|date_format:d/m/Y',
            'options.uestc_print_ready_deadline' => 'required|date_format:d/m/Y',
            'options.teaching_office_contact_glasgow' => 'required|email',
            'options.teaching_office_contact_uestc' => 'required|email',
        ]);

        $dateFields = collect($this->defaultDateOptions)->map(function ($option) {
            return $option['name'];
        });

        collect([
            'options.date_receive_call_for_papers',
            'options.glasgow_staff_submission_deadline',
            'options.uestc_staff_submission_deadline',
            'options.glasgow_internal_moderation_deadline',
            'options.uestc_internal_moderation_deadline',
            'options.date_remind_glasgow_office_externals',
            'options.date_remind_uestc_office_externals',
            'options.glasgow_external_moderation_deadline',
            'options.uestc_external_moderation_deadline',
            'options.glasgow_print_ready_deadline',
            'options.uestc_print_ready_deadline',
            'options.teaching_office_contact_glasgow',
            'options.teaching_office_contact_uestc',
        ])->each(function ($optionName) use ($dateFields) {
            $dbName = str_replace('options.', '', $optionName);
            $value = $this->options[$dbName];
            if ($dateFields->contains($dbName)) {
                $value = Carbon::createFromFormat('d/m/Y', $this->options[$dbName])->format('Y-m-d');
                // if we are changing a notification date, we clear the flag
                // that indicates it's been sent so we can re-send it on the new date
                if ($value != option($dbName . '_email_sent')) {
                    option([$dbName . '_email_sent' => 0]);
                }
            }
            option([$dbName => $value]);
        });

        $this->wasSaved = true;
    }
}

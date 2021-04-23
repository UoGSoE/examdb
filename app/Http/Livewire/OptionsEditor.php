<?php

namespace App\Http\Livewire;

use Appstract\Options\Option;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

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
            'options.staff_submission_deadline' => 'required|date_format:d/m/Y',
            'options.internal_moderation_deadline' => 'required|date_format:d/m/Y',
            'options.date_remind_office_externals' => 'required|date_format:d/m/Y',
            'options.external_moderation_deadline' => 'required|date_format:d/m/Y',
            'options.print_ready_deadline' => 'required|date_format:d/m/Y',
            'options.teaching_office_contact' => 'required|email',
            'options.start_semester_1' => 'required|date_format:d/m/Y',
            'options.start_semester_2' => 'required|date_format:d/m/Y',
            'options.start_semester_3' => 'required|date_format:d/m/Y',
        ]);

        $dateFields = collect($this->defaultDateOptions)->map(function ($option) {
            return $option['name'];
        });

        collect([
            'options.date_receive_call_for_papers',
            'options.staff_submission_deadline',
            'options.internal_moderation_deadline',
            'options.date_remind_office_externals',
            'options.external_moderation_deadline',
            'options.print_ready_deadline',
            'options.teaching_office_contact',
            'start_semester_1',
            'start_semester_2',
            'start_semester_3',
        ])->each(function ($optionName) use ($dateFields) {
            $dbName = str_replace('options.', '', $optionName);
            $value = $this->options[$dbName];
            if ($dateFields->contains($dbName)) {
                $value = Carbon::createFromFormat('d/m/Y', $this->options[$dbName])->format('Y-m-d');
                // if we are changing a notification date, we clear the flag
                // that indicates it's been sent so we can re-send it on the new date
                if ($value != option($dbName.'_email_sent')) {
                    option([$dbName.'_email_sent' => 0]);
                    option([$dbName.'_email_sent_semester_1' => 0]);
                    option([$dbName.'_email_sent_semester_2' => 0]);
                    option([$dbName.'_email_sent_semester_3' => 0]);
                    option([$dbName.'_email_sent_upcoming_semester_1' => 0]);
                    option([$dbName.'_email_sent_reminder_semester_1' => 0]);
                    option([$dbName.'_email_sent_upcoming_semester_2' => 0]);
                    option([$dbName.'_email_sent_reminder_semester_2' => 0]);
                    option([$dbName.'_email_sent_upcoming_semester_3' => 0]);
                    option([$dbName.'_email_sent_reminder_semester_3' => 0]);
                }
            }
            option([$dbName => $value]);
        });

        $this->wasSaved = true;
    }
}

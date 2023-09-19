<?php

namespace App\Http\Livewire;

use Appstract\Options\Option;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class OptionsEditor extends Component
{
    public $options;

    public $daysToWord = [
        1 => 'first',
        2 => 'second',
        3 => 'third',
    ];

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

        // create the new options if they don't exist, otherwise set them to their current value
        foreach (range(1, 3) as $i) {
            $fieldName = 'glasgow_staff_submission_deadline_reminder_'.$i;
            option([$fieldName => option($fieldName, 0)]);
            $fieldName = 'uestc_staff_submission_deadline_reminder_'.$i;
            option([$fieldName => option($fieldName, 0)]);
        }
        option(['glasgow_staff_submission_deadline_overdue_reminder' => option('glasgow_staff_submission_deadline_overdue_reminder', 0)]);
        option(['uestc_staff_submission_deadline_overdue_reminder' => option('uestc_staff_submission_deadline_overdue_reminder', 0)]);

        return Option::all()->flatMap(function ($option) use ($defaultDateOptionKeys) {
            if ($defaultDateOptionKeys->contains($option['key'])) {
                if (! $option->value) {
                    // this is a hacky fix for a change to the 'options' package
                    // at some point between versions it started casting the values as json
                    // which breaks the existing data in the db
                    // this... might fix it...
                    // -- sept 2023
                    $option->value = $option->getRawOriginal('value');
                }
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
            'options.start_semester_1' => 'required|date_format:d/m/Y',
            'options.start_semester_2' => 'required|date_format:d/m/Y',
            'options.start_semester_3' => 'required|date_format:d/m/Y',
            'options.glasgow_staff_submission_deadline_reminder_1' => 'required|integer',
            'options.glasgow_staff_submission_deadline_reminder_2' => 'required|integer',
            'options.glasgow_staff_submission_deadline_reminder_3' => 'required|integer',
            'options.uestc_staff_submission_deadline_reminder_1' => 'required|integer',
            'options.uestc_staff_submission_deadline_reminder_2' => 'required|integer',
            'options.uestc_staff_submission_deadline_reminder_3' => 'required|integer',
            'options.glasgow_staff_submission_deadline_overdue_reminder' => 'required|integer',
            'options.uestc_staff_submission_deadline_overdue_reminder' => 'required|integer',
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
            'options.glasgow_staff_submission_deadline_reminder_1',
            'options.glasgow_staff_submission_deadline_reminder_2',
            'options.glasgow_staff_submission_deadline_reminder_3',
            'options.uestc_staff_submission_deadline_reminder_1',
            'options.uestc_staff_submission_deadline_reminder_2',
            'options.uestc_staff_submission_deadline_reminder_3',
            'options.glasgow_staff_submission_deadline_overdue_reminder',
            'options.uestc_staff_submission_deadline_overdue_reminder',
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

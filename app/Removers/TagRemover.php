<?php

namespace App\Removers;

use App\Models\Advertisements\Advertisement;
use App\Models\Appointments\Appointment;
use App\Models\Category;
use App\Models\Competition;
use App\Models\Courses\Course;
use App\Models\Forms\Form;
use App\Models\LearningMaterial;
use App\Models\LearningMaterialFolder;
use App\Models\News;
use App\Models\Page;
use App\Models\Test;
use App\Models\Voucher;
use App\Models\Webinar;

class TagRemover extends Remover
{
    /**
     * Deletes the tag pivot entries.
     */
    protected function deleteDependees()
    {
        $this->object->users()->detach();
        $this->object->categories()->detach();
        $this->object->categorygroups()->detach();
        $this->object->competitions()->detach();
        $this->object->contentcategories()->detach();
    }

    /**
     * Checks if any tests or competitions have this group as dependency.
     *
     * @return false if clear of blocking dependees, array of strings if not
     */
    public function getBlockingDependees()
    {
        $messages = [];
        $id = $this->object->id;

        $competitions = Competition::whereHas('tags', function ($query) use ($id) {
            $query->where('tag_id', $id);
        })->get();
        foreach ($competitions as $competition) {
            $messages[] = 'Gewinnspiel: '.$competition->getCategoryName().', bis '.$competition->getEndDate()->format('d.m.Y H:i');
        }

        $tests = Test::whereHas('tags', function ($query) {
            return $query->where('tag_id', $this->object->id);
        })->get();
        foreach ($tests as $test) {
            $messages[] = 'Test: '.$test->name;
        }

        $adverts = Advertisement::whereHas('tags', function ($query) {
            return $query->where('tag_id', $this->object->id);
        })->get();
        foreach ($adverts as $advert) {
            $messages[] = 'Banner: '.$advert->name;
        }

        $courses = Course::whereHas('tags', function ($query) {
            return $query->where('tag_id', $this->object->id);
        })->with('translationRelation')
            ->get();
        foreach ($courses as $course) {
            $messages[] = 'Kurs: '.$course->title;
        }

        $learningMaterials = LearningMaterial::whereHas('tags', function ($query) {
            return $query->where('tag_id', $this->object->id);
        })->with('translationRelation')
            ->get();
        foreach ($learningMaterials as $learningMaterial) {
            $messages[] = 'Mediathek: '.$learningMaterial->title;
        }

        $learningMaterialFolders = LearningMaterialFolder::whereHas('tags', function ($query) {
            return $query->where('tag_id', $this->object->id);
        })->with('translationRelation')
            ->get();
        foreach ($learningMaterialFolders as $learningMaterialFolder) {
            $messages[] = 'Mediathek Folder: '.$learningMaterialFolder->name;
        }

        $news = News::whereHas('tags', function ($query) {
            return $query->where('tag_id', $this->object->id);
        })->with('translationRelation')
            ->get();
        foreach ($news as $newsEntry) {
            $messages[] = 'News: '.$newsEntry->title;
        }

        $pages = Page::whereHas('tags', function ($query) {
            return $query->where('tag_id', $this->object->id);
        })->with('translationRelation')
            ->get();
        foreach ($pages as $page) {
            $messages[] = 'Seite: '.$page->title;
        }

        $vouchers = Voucher::whereHas('tags', function ($query) {
            return $query->where('tag_id', $this->object->id);
        })->get();
        foreach ($vouchers as $voucher) {
            $messages[] = 'Voucher: '.$voucher->name;
        }

        $webinars = Webinar::whereHas('tags', function ($query) {
            return $query->where('tag_id', $this->object->id);
        })->get();
        foreach ($webinars as $webinar) {
            $messages[] = 'Webinare: '.$webinar->topic;
        }

        $forms = Form::whereHas('tags', function ($query) {
            return $query->where('tag_id', $this->object->id);
        })->get();
        foreach ($forms as $form) {
            $messages[] = 'Formulare: '. $form->title;
        }

        $appointments = Appointment::whereHas('tags', function ($query) {
            return $query->where('tag_id', $this->object->id);
        })->get();
        foreach ($appointments as $appointment) {
            $messages[] = 'Termine: '. $appointment->name;
        }

        $categories = Category::whereHas('tags', function ($query) {
            return $query->where('tag_id', $this->object->id);
        })->with('translationRelation')
            ->get();
        foreach ($categories as $category) {
            $messages[] = 'Kategorie: '.$category->name;
        }

        return count($messages) ? $messages : false;
    }

    /**
     * Executes the actual deletion.
     *
     * @return true
     */
    protected function doDeletion()
    {
        $this->deleteDependees();
        $this->object->forceDelete();

        return true;
    }
}

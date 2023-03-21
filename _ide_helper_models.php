<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\AccessLog
 *
 * @property int $id
 * @property int $user_id
 * @property int $action
 * @property int|null $ip
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array|null $meta
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLog whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLog whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessLog whereUserId($value)
 */
	class IdeHelperAccessLog {}
}

namespace App\Models\Advertisements{
/**
 * App\Models\Advertisements\Advertisement
 *
 * @property int $id
 * @property int $app_id
 * @property string $name
 * @property int $visible
 * @property int $is_ad
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Advertisements\AdvertisementTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Advertisements\AdvertisementPosition[] $positions
 * @property-read int|null $positions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Tag[] $tags
 * @property-read int|null $tags_count
 * @method static Builder|Advertisement newModelQuery()
 * @method static Builder|Advertisement newQuery()
 * @method static Builder|Advertisement public($app_id)
 * @method static Builder|Advertisement query()
 * @method static Builder|Advertisement visibleToUser(\App\Models\User $user)
 * @method static Builder|Advertisement whereAppId($value)
 * @method static Builder|Advertisement whereCreatedAt($value)
 * @method static Builder|Advertisement whereId($value)
 * @method static Builder|Advertisement whereIsAd($value)
 * @method static Builder|Advertisement whereName($value)
 * @method static Builder|Advertisement whereUpdatedAt($value)
 * @method static Builder|Advertisement whereVisible($value)
 */
	class IdeHelperAdvertisement {}
}

namespace App\Models\Advertisements{
/**
 * App\Models\Advertisements\AdvertisementPosition
 *
 * @property int $id
 * @property int $advertisement_id
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Advertisements\Advertisement $advertisement
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementPosition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementPosition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementPosition query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementPosition whereAdvertisementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementPosition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementPosition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementPosition wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementPosition whereUpdatedAt($value)
 */
	class IdeHelperAdvertisementPosition {}
}

namespace App\Models\Advertisements{
/**
 * App\Models\Advertisements\AdvertisementTranslation
 *
 * @property int $id
 * @property int $advertisement_id
 * @property string $language
 * @property string|null $description
 * @property string|null $link
 * @property string|null $rectangle_image_url
 * @property string|null $leaderboard_image_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Advertisements\Advertisement $advertisement
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation whereAdvertisementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation whereLeaderboardImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation whereRectangleImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertisementTranslation whereUpdatedAt($value)
 */
	class IdeHelperAdvertisementTranslation {}
}

namespace App\Models{
/**
 * App\Models\AnalyticsEvent
 *
 * @property int $id
 * @property int $app_id
 * @property int|null $user_id
 * @property int|null $foreign_id
 * @property int|null $foreign_type
 * @property int $type
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\App|null $app
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $foreign
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $foreignTags
 * @property-read int|null $foreign_tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $userTags
 * @property-read int|null $user_tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|AnalyticsEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AnalyticsEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AnalyticsEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder|AnalyticsEvent whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AnalyticsEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AnalyticsEvent whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AnalyticsEvent whereForeignType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AnalyticsEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AnalyticsEvent whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AnalyticsEvent whereUserId($value)
 */
	class IdeHelperAnalyticsEvent {}
}

namespace App\Models{
/**
 * App\Models\App.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $users
 * @property-read \Illuminate\Database\Eloquent\Collection|Category[] $categories
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $rounds_per_game
 * @property int $answers_per_question
 * @property int $questions_per_round
 * @property int $tos_id
 * @property string $default_avatar
 * @property string $app_hosted_at
 * @property int $samba_id
 * @property string $samba_token
 * @property string $support_phone_number
 * @property-read \Illuminate\Database\Eloquent\Collection|Game[] $games
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereRoundsPerGame($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereAnswersPerQuestion($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereQuestionsPerRound($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereAppHostedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereTosId($value)
 * @mixin \Eloquent
 * @property string $terms
 * @property string $contact_information Has to be saved in the following format: "$phone;$email"
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereTerms($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereContactInformation($value)
 * @property string $notification_mails
 * @property string|null $xapi_token
 * @property string $internal_notes
 * @property int|null $user_licences
 * @property-read int|null $categories_count
 * @property-read int|null $games_count
 * @property-read mixed $views
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Page[] $pages
 * @property-read int|null $pages_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TagGroup[] $tagGroups
 * @property-read int|null $tag_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Viewcount[] $viewcounts
 * @property-read int|null $viewcounts_count
 * @method static \Illuminate\Database\Eloquent\Builder|App newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|App newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|App query()
 * @method static \Illuminate\Database\Eloquent\Builder|App whereDefaultAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|App whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|App whereNotificationMails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|App whereSambaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|App whereSambaToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|App whereSupportPhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|App whereUserLicences($value)
 * @method static \Illuminate\Database\Eloquent\Builder|App whereXapiToken($value)
 * @property string|null $learninglocker_id
 * @property-read mixed $app_name
 * @property-read mixed $logo_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\Course[] $inheritedCourseTemplates
 * @property-read int|null $inherited_course_templates_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AppProfile[] $profiles
 * @property-read int|null $profiles_count
 * @property-read \Illuminate\Database\Eloquent\Collection|App[] $templateInheritanceChildren
 * @property-read int|null $template_inheritance_children_count
 * @property-read \Illuminate\Database\Eloquent\Collection|App[] $templateInheritanceParents
 * @property-read int|null $template_inheritance_parents_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserRole[] $userRoles
 * @property-read int|null $user_roles_count
 * @method static \Database\Factories\AppFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|App whereLearninglockerId($value)
 */
	class IdeHelperApp {}
}

namespace App\Models{
/**
 * App\Models\AppProfile
 *
 * @property int $id
 * @property string $name
 * @property int $app_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AppProfileSetting[] $settings
 * @property-read int|null $settings_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AppProfileTag[] $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfile whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfile whereUpdatedAt($value)
 * @property int $is_default
 * @property-read mixed $app_hosted_at
 * @method static \Database\Factories\AppProfileFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfile whereIsDefault($value)
 */
	class IdeHelperAppProfile {}
}

namespace App\Models{
/**
 * App\Models\AppProfileHomeComponent
 *
 * @property int $id
 * @property int $app_profile_id
 * @property int $position
 * @property string $type
 * @property int $visible
 * @property array|null $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AppProfile|null $appProfile
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileHomeComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileHomeComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileHomeComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileHomeComponent whereAppProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileHomeComponent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileHomeComponent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileHomeComponent wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileHomeComponent whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileHomeComponent whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileHomeComponent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileHomeComponent whereVisible($value)
 */
	class IdeHelperAppProfileHomeComponent {}
}

namespace App\Models{
/**
 * App\Models\AppProfileSetting
 *
 * @property int $id
 * @property int $app_profile_id
 * @property string $key
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AppProfile $appProfile
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileSetting whereAppProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileSetting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileSetting whereValue($value)
 */
	class IdeHelperAppProfileSetting {}
}

namespace App\Models{
/**
 * App\Models\AppProfileTag
 *
 * @property int $id
 * @property int $app_profile_id
 * @property int $tag_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AppProfile $appProfile
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileTag query()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileTag whereAppProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileTag whereTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileTag whereUpdatedAt($value)
 */
	class IdeHelperAppProfileTag {}
}

namespace App\Models{
/**
 * App\Models\AppRating
 *
 * @property int $id
 * @property int $user_id
 * @property float $rating
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|AppRating newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppRating newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppRating query()
 * @method static \Illuminate\Database\Eloquent\Builder|AppRating whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppRating whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppRating whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppRating whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppRating whereUserId($value)
 */
	class IdeHelperAppRating {}
}

namespace App\Models{
/**
 * App\Models\AppSetting.
 *
 * @property int $id
 * @property int $app_id
 * @property string $key
 * @property string $value
 * @property-read \App\Models\App $app
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AppSetting whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AppSetting whereAppId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AppSetting whereKey($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AppSetting whereValue($value)
 * @mixin \Eloquent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting whereUpdatedAt($value)
 */
	class IdeHelperAppSetting {}
}

namespace App\Models\Appointments{
/**
 * App\Models\Appointments\Appointment
 *
 * @property int $id
 * @property int $app_id
 * @property int $type
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int $is_cancelled
 * @property int $has_reminder
 * @property int|null $reminder_time
 * @property int|null $reminder_unit_type
 * @property string|null $location
 * @property int $created_by_id
 * @property int $last_updated_by_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $is_draft
 * @property string|null $send_reminder_at
 * @property int $send_notification
 * @property string|null $last_notification_sent_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Appointments\AppointmentTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \App\Models\App|null $app
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read \App\Models\User|null $createdBy
 * @property-read Boolean $is_reusable_clone
 * @property-read \App\Models\User|null $lastUpdatedBy
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment ofApp($appId)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment tagRights(?\App\Models\User $admin = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment visible()
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereCreatedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereHasReminder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereIsCancelled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereIsDraft($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereLastNotificationSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereLastUpdatedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereReminderTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereReminderUnitType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereSendNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereSendReminderAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appointment whereUpdatedAt($value)
 */
	class IdeHelperAppointment {}
}

namespace App\Models\Appointments{
/**
 * App\Models\Appointments\AppointmentTranslation
 *
 * @property int $id
 * @property string $language
 * @property int $appointment_id
 * @property string $name
 * @property string|null $description
 * @property string|null $cover_image_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $cover_image_url_transformation_id
 * @property-read \App\Models\Appointments\Appointment|null $appointment
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 * @method static \Illuminate\Database\Eloquent\Builder|AppointmentTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppointmentTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppointmentTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|AppointmentTranslation whereAppointmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppointmentTranslation whereCoverImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppointmentTranslation whereCoverImageUrlTransformationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppointmentTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppointmentTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppointmentTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppointmentTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppointmentTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppointmentTranslation whereUpdatedAt($value)
 */
	class IdeHelperAppointmentTranslation {}
}

namespace App\Models{
/**
 * App\Models\AuthToken
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|AuthToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthToken whereUserId($value)
 */
	class IdeHelperAuthToken {}
}

namespace App\Models{
/**
 * App\Models\AzureVideo
 *
 * @property int $id
 * @property int $app_id
 * @property int $progress
 * @property int $status
 * @property string|null $finished_at
 * @property string|null $job_id
 * @property string|null $input_asset_id
 * @property string|null $output_asset_id
 * @property string|null $streaming_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\App $app
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo query()
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereInputAssetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereOutputAssetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereStreamingUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereUpdatedAt($value)
 * @property string|null $job_name
 * @property string|null $input_asset_name
 * @property string|null $output_asset_name
 * @property string|null $subtitles_language
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AzureVideoSubtitle[] $subtitles
 * @property-read int|null $subtitles_count
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereInputAssetName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereJobName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereOutputAssetName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideo whereSubtitlesLanguage($value)
 */
	class IdeHelperAzureVideo {}
}

namespace App\Models{
/**
 * App\Models\AzureVideoSubtitle
 *
 * @property int $id
 * @property string $azure_video_output_asset_id
 * @property string $language
 * @property int $progress
 * @property int $status
 * @property string|null $finished_at
 * @property string $asset_name
 * @property string $job_name
 * @property string|null $streaming_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AzureVideo|null $azureVideo
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideoSubtitle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideoSubtitle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideoSubtitle query()
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideoSubtitle whereAssetName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideoSubtitle whereAzureVideoOutputAssetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideoSubtitle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideoSubtitle whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideoSubtitle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideoSubtitle whereJobName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideoSubtitle whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideoSubtitle whereProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideoSubtitle whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideoSubtitle whereStreamingUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AzureVideoSubtitle whereUpdatedAt($value)
 */
	class IdeHelperAzureVideoSubtitle {}
}

namespace App\Models{
/**
 * App\Models\Category.
 *
 * @property-read \App\Models\App $app
 * @property-read \App\Models\Categorygroup $categorygroup
 * @property int $id
 * @property int $app_id
 * @property string $name
 * @property int $points
 * @property bool $active
 * @property int $categorygroup_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category ofApp($app_id)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereAppId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereActive($value)
 * @mixin \Eloquent
 * @property string|null $cover_image
 * @property string|null $cover_image_url
 * @property string|null $category_icon
 * @property string|null $category_icon_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Question[] $allQuestions
 * @property-read int|null $all_questions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CategoryTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CategoryTranslation[] $category_translation
 * @property-read int|null $category_translation_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Competition[] $competitions
 * @property-read int|null $competitions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GameRound[] $gameRounds
 * @property-read int|null $game_rounds_count
 * @property-read mixed $icon_url
 * @property-read mixed $image_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CategoryHider[] $hiders
 * @property-read int|null $hiders_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\IndexCard[] $indexCards
 * @property-read int|null $index_cards_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Question[] $questions
 * @property-read int|null $questions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SuggestedQuestion[] $suggestedQuestions
 * @property-read int|null $suggested_questions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCategoryIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCategoryIconUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCategorygroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCoverImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCoverImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category wherePoints($value)
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 * @method static \Illuminate\Database\Eloquent\Builder|Category tagRights(?\App\Models\User $admin = null)
 */
	class IdeHelperCategory {}
}

namespace App\Models{
/**
 * App\Models\Category.
 *
 * @property int $id
 * @property int $category_id
 * @property int $scope
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category $category
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHider query()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHider s()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHider whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHider whereScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHider whereUpdatedAt($value)
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 */
	class IdeHelperCategoryHider {}
}

namespace App\Models{
/**
 * App\Models\CategoryTranslation
 *
 * @property int $id
 * @property int $category_id
 * @property string $language
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category $category
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation whereUpdatedAt($value)
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 */
	class IdeHelperCategoryTranslation {}
}

namespace App\Models{
/**
 * App\Models\Categorygroup.
 *
 * @property-read \App\Models\App $app
 * @property int $id
 * @property int $app_id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Category[] $categories
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuizTeam whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuizTeam whereAppId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuizTeam whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuizTeam whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuizTeam whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuizTeam whereOwnerId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereTagIds($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CategorygroupTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|Categorygroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Categorygroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Categorygroup ofApp($appId)
 * @method static \Illuminate\Database\Eloquent\Builder|Categorygroup query()
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 */
	class IdeHelperCategorygroup {}
}

namespace App\Models{
/**
 * App\Models\CategorygroupTranslation
 *
 * @property int $id
 * @property int $categorygroup_id
 * @property string $language
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Categorygroup $categorygroup
 * @method static \Illuminate\Database\Eloquent\Builder|CategorygroupTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategorygroupTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategorygroupTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|CategorygroupTranslation whereCategorygroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategorygroupTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategorygroupTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategorygroupTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategorygroupTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategorygroupTranslation whereUpdatedAt($value)
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 */
	class IdeHelperCategorygroupTranslation {}
}

namespace App\Models{
/**
 * App\Models\CertificateTemplate
 *
 * @property int $id
 * @property int $test_id
 * @property string $background_image
 * @property string|null $background_image_url
 * @property string $elements
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $background_image_size
 * @property-read \App\Models\Test $test
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate whereBackgroundImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate whereBackgroundImageSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate whereBackgroundImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate whereElements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplate whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CertificateTemplateTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 */
	class IdeHelperCertificateTemplate {}
}

namespace App\Models{
/**
 * App\Models\CertificateTemplateTranslation
 *
 * @property int $id
 * @property int $certificate_template_id
 * @property string $language
 * @property string|null $background_image
 * @property string|null $background_image_url
 * @property string|null $background_image_size
 * @property string|null $elements
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CertificateTemplate|null $certificateTemplate
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplateTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplateTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplateTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplateTranslation whereBackgroundImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplateTranslation whereBackgroundImageSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplateTranslation whereBackgroundImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplateTranslation whereCertificateTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplateTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplateTranslation whereElements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplateTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplateTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CertificateTemplateTranslation whereUpdatedAt($value)
 */
	class IdeHelperCertificateTemplateTranslation {}
}

namespace App\Models{
/**
 * App\Models\CloneRecord
 *
 * @property int $id
 * @property int $type
 * @property int $source_id
 * @property int $target_app_id
 * @property int $target_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CloneRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CloneRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CloneRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|CloneRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CloneRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CloneRecord whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CloneRecord whereTargetAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CloneRecord whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CloneRecord whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CloneRecord whereUpdatedAt($value)
 */
	class IdeHelperCloneRecord {}
}

namespace App\Models\Comments{
/**
 * Class Comment
 *
 * @package App\Models\Comments
 * @property int $id
 * @property int $app_id
 * @property int $author_id
 * @property int $foreign_type
 * @property int $foreign_id
 * @property int $parent_id
 * @property int $deleted_by_id
 * @property string $body
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property Collection $reports
 * @property App $app
 * @property User $author
 * @property User $deletedBy
 * @property Collection $commentable
 * @property-read int|null $reports_count
 * @method static \Illuminate\Database\Eloquent\Builder|Comment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereDeletedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereForeignType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comments\CommentAttachment[] $attachments
 * @property-read int|null $attachments_count
 * @property-read Comment|null $parentComment
 * @property-read \Illuminate\Database\Eloquent\Collection|Comment[] $replies
 * @property-read int|null $replies_count
 * @method static \Illuminate\Database\Eloquent\Builder|Comment ofApp($appId)
 */
	class IdeHelperComment {}
}

namespace App\Models\Comments{
/**
 * Class CommentAttachment
 *
 * @property int $id
 * @property int $comment_id
 * @property string $file
 * @property string $file_url
 * @property string $file_type
 * @property int $file_size_kb
 * @property string $original_filename
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Comments\Comment|null $comment
 * @method static \Illuminate\Database\Eloquent\Builder|CommentAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommentAttachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommentAttachment query()
 * @method static \Illuminate\Database\Eloquent\Builder|CommentAttachment whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentAttachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentAttachment whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentAttachment whereFileSizeKb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentAttachment whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentAttachment whereFileUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentAttachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentAttachment whereOriginalFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentAttachment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class IdeHelperCommentAttachment {}
}

namespace App\Models\Comments{
/**
 * Class CommentReport
 *
 * @package App\Models\Comments
 * @property int $id
 * @property int $reporter_id
 * @property int $comment_id
 * @property int $status_manager_id
 * @property int $reason
 * @property string $reason_explanation
 * @property int $status
 * @property string $status_explanation
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property Comment $comment
 * @property User $reporter
 * @property User $statusManager
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport query()
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereReasonExplanation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereReporterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereStatusExplanation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereStatusManagerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class IdeHelperCommentReport {}
}

namespace App\Models{
/**
 * App\Models\Competition.
 *
 * @property-read \App\Models\App $app
 * @property int $id
 * @property int $app_id
 * @property int $category_id
 * @property int $quiz_team_id
 * @property int $duration
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $start_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Category $category
 * @property-read \App\Models\QuizTeam $quizTeam
 * @method static Builder|Competition whereId($value)
 * @method static Builder|Competition whereAppId($value)
 * @method static Builder|Competition whereCategoryId($value)
 * @method static Builder|Competition whereQuizTeamId($value)
 * @method static Builder|Competition whereDuration($value)
 * @method static Builder|Competition whereCreatedAt($value)
 * @method static Builder|Competition whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property string|null $title
 * @property string|null $notification_sent_at
 * @property string|null $cover_image
 * @property string|null $cover_image_url
 * @property string|null $description
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comments\Comment[] $comments
 * @property-read int|null $comment_count
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|Competition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Competition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Competition query()
 * @method static \Illuminate\Database\Eloquent\Builder|Competition tagRights()
 * @method static \Illuminate\Database\Eloquent\Builder|Competition tagRightsJoin($tagIds = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereCoverImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereCoverImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereNotificationSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereTitle($value)
 * @property-read int|null $comments_count
 */
	class IdeHelperCompetition {}
}

namespace App\Models\ContentCategories{
/**
 * App\Models\ContentCategories\ContentCategory
 *
 * @property int $id
 * @property int $app_id
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContentCategories\ContentCategoryTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContentCategories\ContentCategoryRelation[] $contentCategoryRelations
 * @property-read int|null $content_category_relations_count
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategory whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategory whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Database\Factories\ContentCategories\ContentCategoryFactory factory(...$parameters)
 */
	class IdeHelperContentCategory {}
}

namespace App\Models\ContentCategories{
/**
 * App\Models\ContentCategories\ContentCategoryRelation
 *
 * @property int $id
 * @property int $content_category_id
 * @property int $foreign_id
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ContentCategories\ContentCategory $contentCategory
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryRelation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryRelation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryRelation query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryRelation whereContentCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryRelation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryRelation whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryRelation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryRelation whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryRelation whereUpdatedAt($value)
 */
	class IdeHelperContentCategoryRelation {}
}

namespace App\Models\ContentCategories{
/**
 * App\Models\ContentCategories\ContentCategoryTranslation
 *
 * @property int $id
 * @property int $content_category_id
 * @property string $language
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ContentCategories\ContentCategory $contentCategory
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryTranslation whereContentCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryTranslation whereUpdatedAt($value)
 */
	class IdeHelperContentCategoryTranslation {}
}

namespace App\Models\Courses{
/**
 * App\Models\Courses\Course
 *
 * @property int $id
 * @property int $app_id
 * @property \Illuminate\Support\Carbon|null $available_from
 * @property \Illuminate\Support\Carbon|null $available_until
 * @property int $visible
 * @property int $duration_type
 * @property int $participation_duration
 * @property int $participation_duration_type
 * @property string|null $cover_image_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $preview_enabled
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|ContentCategory[] $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseChapter[] $chapters
 * @property-read int|null $chapters_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseContent[] $contents
 * @property-read int|null $contents_count
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $managers
 * @property-read int|null $managers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseParticipation[] $participations
 * @property-read int|null $participations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Tag[] $previewTags
 * @property-read int|null $preview_tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseContent[] $visibleContents
 * @property-read int|null $visible_contents_count
 * @method static \Illuminate\Database\Eloquent\Builder|Course newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Course newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Course query()
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereAvailableFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereAvailableUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereCoverImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course wherePreviewEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereVisible($value)
 * @property int $is_mandatory
 * @property int $is_template
 * @property int|null $creator_id
 * @property string|null $archived_at
 * @property int $send_passed_course_mail
 * @property int $is_repeating
 * @property int|null $repetition_interval
 * @property int|null $repetition_interval_type
 * @property int|null $repetition_count
 * @property int|null $time_limit
 * @property int|null $time_limit_type
 * @property int|null $parent_course_id
 * @property int $send_new_course_notification
 * @property int $send_repetition_course_reminder
 * @property int $has_individual_attendees
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $awardTags
 * @property-read int|null $award_tags_count
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comments\Comment[] $comments
 * @property-read int|null $comments_count
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Reminder|null $earliestReminder
 * @property-read mixed $available_status
 * @property-read Boolean $is_reusable_clone
 * @property-read \Carbon\Carbon|null $next_repetition_date
 * @property-read mixed $track_wbts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $individualAttendees
 * @property-read int|null $individual_attendees_count
 * @property-read Course|null $latestRepeatedCourse
 * @property-read Course|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Reminder[] $reminders
 * @property-read int|null $reminders_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $retractTags
 * @property-read int|null $retract_tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\App[] $templateInheritanceApps
 * @property-read int|null $template_inheritance_apps_count
 * @method static \Illuminate\Database\Eloquent\Builder|Course currentAndPast()
 * @method static \Database\Factories\Courses\CourseFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Course mandatory()
 * @method static \Illuminate\Database\Eloquent\Builder|Course ofApp($appId)
 * @method static \Illuminate\Database\Eloquent\Builder|Course repeatingTemplate()
 * @method static \Illuminate\Database\Eloquent\Builder|Course tagRights(?\App\Models\User $admin = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Course template()
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereArchivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereDurationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereHasIndividualAttendees($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereIsMandatory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereIsRepeating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereIsTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereParentCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereParticipationDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereParticipationDurationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereRepetitionCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereRepetitionInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereRepetitionIntervalType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereSendNewCourseNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereSendPassedCourseMail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereSendRepetitionCourseReminder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereTimeLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereTimeLimitType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course withTemplates()
 */
	class IdeHelperCourse {}
}

namespace App\Models\Courses{
/**
 * App\Models\Courses\CourseAccessRequest
 *
 * @property int $id
 * @property int $course_id
 * @property int $user_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Courses\Course $course
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|CourseAccessRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseAccessRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseAccessRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseAccessRequest whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseAccessRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseAccessRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseAccessRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseAccessRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseAccessRequest whereUserId($value)
 * @mixin \Eloquent
 */
	class IdeHelperCourseAccessRequest {}
}

namespace App\Models\Courses{
/**
 * App\Models\Courses\CourseChapter
 *
 * @property int $id
 * @property int $course_id
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseChapterTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseContent[] $contents
 * @property-read int|null $contents_count
 * @property-read \App\Models\Courses\Course $course
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapter query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapter whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapter wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapter whereUpdatedAt($value)
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 * @method static \Database\Factories\Courses\CourseChapterFactory factory(...$parameters)
 */
	class IdeHelperCourseChapter {}
}

namespace App\Models\Courses{
/**
 * App\Models\Courses\CourseChapterTranslation
 *
 * @property int $id
 * @property int $course_chapter_id
 * @property string $language
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Courses\CourseChapter $chapter
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapterTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapterTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapterTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapterTranslation whereCourseChapterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapterTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapterTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapterTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapterTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseChapterTranslation whereUpdatedAt($value)
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 */
	class IdeHelperCourseChapterTranslation {}
}

namespace App\Models\Courses{
/**
 * App\Models\Courses\CourseContent
 *
 * @property int $id
 * @property int $course_chapter_id
 * @property int $type
 * @property int|null $foreign_id
 * @property int $position
 * @property int $visible
 * @property int $duration
 * @property int|null $pass_percentage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $is_test
 * @property int $show_correct_result
 * @property int|null $repetitions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseContentTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseContentAttachment[] $attachments
 * @property-read int|null $attachments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseContentAttempt[] $attempts
 * @property-read int|null $attempts_count
 * @property-read \App\Models\Courses\CourseChapter $chapter
 * @property-read \App\Models\Courses\Course|null $course
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $relatable
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereCourseChapterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereIsTest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent wherePassPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereRepetitions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereShowCorrectResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent whereVisible($value)
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @method static \Database\Factories\Courses\CourseContentFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContent tagRights(?\App\Models\User $admin = null)
 */
	class IdeHelperCourseContent {}
}

namespace App\Models\Courses{
/**
 * App\Models\Courses\CourseContentAttachment
 *
 * @property int $id
 * @property int $course_content_id
 * @property int $position
 * @property int $type
 * @property int $foreign_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $attachment
 * @property-read \App\Models\Courses\CourseContent $content
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment whereCourseContentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttachment whereUpdatedAt($value)
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 */
	class IdeHelperCourseContentAttachment {}
}

namespace App\Models\Courses{
/**
 * App\Models\Courses\CourseContentAttempt
 *
 * @property int $id
 * @property int $course_content_id
 * @property int $course_participation_id
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property int|null $passed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseContentAttemptAttachment[] $attachments
 * @property-read int|null $attachments_count
 * @property-read \App\Models\Courses\CourseContent $content
 * @property-read mixed $certificate_download_url
 * @property-read \App\Models\Courses\CourseParticipation $participation
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt whereCourseContentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt whereCourseParticipationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt wherePassed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttempt whereUpdatedAt($value)
 * @property-read mixed $backend_certificate_download_url
 * @method static \Database\Factories\Courses\CourseContentAttemptFactory factory(...$parameters)
 */
	class IdeHelperCourseContentAttempt {}
}

namespace App\Models\Courses{
/**
 * App\Models\Courses\CourseContentAttemptAttachment
 *
 * @property int $id
 * @property int $course_content_attempt_id
 * @property int $course_content_attachment_id
 * @property string $value
 * @property int|null $passed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Courses\CourseContentAttachment $attachment
 * @property-read \App\Models\Courses\CourseContentAttempt $attempt
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment whereCourseContentAttachmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment whereCourseContentAttemptId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment wherePassed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentAttemptAttachment whereValue($value)
 */
	class IdeHelperCourseContentAttemptAttachment {}
}

namespace App\Models\Courses{
/**
 * App\Models\Courses\CourseContentTranslation
 *
 * @property int $id
 * @property int $course_content_id
 * @property string $language
 * @property string $title
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Courses\CourseContent $content
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation whereCourseContentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseContentTranslation whereUpdatedAt($value)
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read \App\Models\Courses\CourseContent|null $courseContent
 * @property-read Boolean $is_reusable_clone
 */
	class IdeHelperCourseContentTranslation {}
}

namespace App\Models\Courses{
/**
 * App\Models\Courses\CourseParticipation
 *
 * @property int $id
 * @property int $course_id
 * @property int $user_id
 * @property int|null $passed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $finished_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseContentAttempt[] $contentAttempts
 * @property-read int|null $content_attempts_count
 * @property-read \App\Models\Courses\Course $course
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation wherePassed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseParticipation whereUserId($value)
 * @method static \Database\Factories\Courses\CourseParticipationFactory factory(...$parameters)
 */
	class IdeHelperCourseParticipation {}
}

namespace App\Models\Courses{
/**
 * App\Models\Courses\CourseTranslation
 *
 * @property int $id
 * @property string $language
 * @property int $course_id
 * @property string $title
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Courses\Course $course
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereUpdatedAt($value)
 * @property string|null $request_access_link
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 * @method static \Illuminate\Database\Eloquent\Builder|CourseTranslation whereRequestAccessLink($value)
 */
	class IdeHelperCourseTranslation {}
}

namespace App\Models{
/**
 * App\Models\DirectMessage
 *
 * @property int $id
 * @property int $app_id
 * @property int $recipient_id
 * @property int $sender_id
 * @property string $body
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\App|null $app
 * @property-read \App\Models\User|null $recipient
 * @property-read \App\Models\User|null $sender
 * @method static \Illuminate\Database\Eloquent\Builder|DirectMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DirectMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DirectMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder|DirectMessage whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DirectMessage whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DirectMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DirectMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DirectMessage whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DirectMessage whereRecipientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DirectMessage whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DirectMessage whereUpdatedAt($value)
 */
	class IdeHelperDirectMessage {}
}

namespace App\Models{
/**
 * App\Models\EventHistory
 *
 * @property int $id
 * @property int $user_id
 * @property int $type
 * @property string $foreign_id
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory whereUserId($value)
 */
	class IdeHelperEventHistory {}
}

namespace App\Models{
/**
 * App\Models\FcmToken
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string|null $app_store_id
 * @property string|null $platform
 * @property string|null $model
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereAppStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereUserId($value)
 */
	class IdeHelperFcmToken {}
}

namespace App\Models\Forms{
/**
 * App\Models\Forms\Form
 *
 * @property int $id
 * @property int $app_id
 * @property int $is_draft
 * @property int $is_archived
 * @property int $created_by_id
 * @property int $last_updated_by_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Forms\FormTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Forms\FormAnswer[] $answers
 * @property-read int|null $answers_count
 * @property-read \App\Models\App|null $app
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContentCategories\ContentCategory[] $categories
 * @property-read int|null $categories_count
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read \App\Models\User|null $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Forms\FormField[] $fields
 * @property-read int|null $fields_count
 * @property-read Boolean $is_reusable_clone
 * @property-read \App\Models\User|null $lastUpdatedBy
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @method static \Database\Factories\Forms\FormFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Form newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Form newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Form ofApp($appId)
 * @method static \Illuminate\Database\Eloquent\Builder|Form query()
 * @method static \Illuminate\Database\Eloquent\Builder|Form tagRights(?\App\Models\User $admin = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Form visible()
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereCreatedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereIsArchived($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereIsDraft($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereLastUpdatedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereUpdatedAt($value)
 */
	class IdeHelperForm {}
}

namespace App\Models\Forms{
/**
 * App\Models\Forms\FormAnswer
 *
 * @property int $id
 * @property int $form_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $foreign_type
 * @property int|null $foreign_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Forms\FormAnswerField[] $fields
 * @property-read int|null $fields_count
 * @property-read \App\Models\Forms\Form|null $form
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $relatable
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer whereForeignType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer whereFormId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswer whereUserId($value)
 */
	class IdeHelperFormAnswer {}
}

namespace App\Models\Forms{
/**
 * App\Models\Forms\FormAnswerField
 *
 * @property int $id
 * @property int $form_answer_id
 * @property int $form_field_id
 * @property string $answer
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Forms\FormAnswer|null $formAnswer
 * @property-read \App\Models\Forms\FormField|null $formField
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswerField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswerField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswerField query()
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswerField whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswerField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswerField whereFormAnswerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswerField whereFormFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswerField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormAnswerField whereUpdatedAt($value)
 */
	class IdeHelperFormAnswerField {}
}

namespace App\Models\Forms{
/**
 * App\Models\Forms\FormField
 *
 * @property int $id
 * @property int $form_id
 * @property int $is_required
 * @property int $type
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Forms\FormFieldTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Forms\FormAnswerField[] $answers
 * @property-read int|null $answers_count
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read \App\Models\Forms\Form|null $form
 * @property-read Boolean $is_reusable_clone
 * @method static \Database\Factories\Forms\FormFieldFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|FormField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormField query()
 * @method static \Illuminate\Database\Eloquent\Builder|FormField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormField whereFormId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormField whereIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormField wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormField whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormField whereUpdatedAt($value)
 */
	class IdeHelperFormField {}
}

namespace App\Models\Forms{
/**
 * App\Models\Forms\FormFieldTranslation
 *
 * @property int $id
 * @property string $language
 * @property int $form_field_id
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read \App\Models\Forms\FormField|null $field
 * @property-read Boolean $is_reusable_clone
 * @method static \Illuminate\Database\Eloquent\Builder|FormFieldTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormFieldTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormFieldTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|FormFieldTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormFieldTranslation whereFormFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormFieldTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormFieldTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormFieldTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormFieldTranslation whereUpdatedAt($value)
 */
	class IdeHelperFormFieldTranslation {}
}

namespace App\Models\Forms{
/**
 * App\Models\Forms\FormTranslation
 *
 * @property int $id
 * @property string $language
 * @property int $form_id
 * @property string $title
 * @property string $cover_image_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read \App\Models\Forms\Form|null $form
 * @property-read Boolean $is_reusable_clone
 * @method static \Illuminate\Database\Eloquent\Builder|FormTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|FormTranslation whereCoverImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormTranslation whereFormId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormTranslation whereUpdatedAt($value)
 */
	class IdeHelperFormTranslation {}
}

namespace App\Models{
/**
 * App\Models\FrontendTranslation
 *
 * @property int $id
 * @property int $app_id
 * @property string $language
 * @property string $key
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation whereUpdatedAt($value)
 * @property int $app_profile_id
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation whereAppProfileId($value)
 */
	class IdeHelperFrontendTranslation {}
}

namespace App\Models{
/**
 * Class Game.
 *
 * @property int $app_id
 * @property int $player1_id
 * @property int $player2_id
 * @property int $player1_joker_available
 * @property int $player2_joker_available
 * @property int $status
 * @property int $winner
 * @property-read \App\Models\App $app
 * @property-read \App\Models\User $player1
 * @property-read \App\Models\User $player2
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GameRound[] $gameRounds
 * @property int $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game ofUser($userId)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game active()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game finished()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereAppId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game wherePlayer1Id($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game wherePlayer2Id($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game wherePlayer1JokerAvailable($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game wherePlayer2JokerAvailable($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $legacy_turn_order
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GameQuestion[] $gameQuestions
 * @property-read int|null $game_questions_count
 * @property-read int|null $game_rounds_count
 * @method static \Illuminate\Database\Eloquent\Builder|Game newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Game newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Game ofApp($appId)
 * @method static \Illuminate\Database\Eloquent\Builder|Game query()
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereLegacyTurnOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereWinner($value)
 */
	class IdeHelperGame {}
}

namespace App\Models{
/**
 * Class GamePoint.
 *
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property int $reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|GamePoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GamePoint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GamePoint query()
 * @method static \Illuminate\Database\Eloquent\Builder|GamePoint whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GamePoint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GamePoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GamePoint whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GamePoint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GamePoint whereUserId($value)
 */
	class IdeHelperGamePoint {}
}

namespace App\Models{
/**
 * App\Models\GameQuestion.
 *
 * @property-read \App\Models\GameRound $gameRound
 * @property-read \App\Models\Question $question
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GameQuestionAnswer[] $gameQuestionAnswers
 * @property int $id
 * @property int $game_round_id
 * @property int $question_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestion ofRound($roundId)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestion ofQuestion($questionId)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestion whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestion whereGameRoundId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestion whereQuestionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestion whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read int|null $game_question_answers_count
 * @method static \Illuminate\Database\Eloquent\Builder|GameQuestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GameQuestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GameQuestion query()
 */
	class IdeHelperGameQuestion {}
}

namespace App\Models{
/**
 * App\Models\GameQuestionAnswer.
 *
 * @property-read \App\Models\GameQuestion $gameQuestion
 * @property-read \App\Models\User $user
 * @property-read \App\Models\QuestionAnswer $questionAnswer
 * @property int $id
 * @property int $game_question_id
 * @property int $user_id
 * @property int $question_answer_id Is -1 if the question wasn't answered in time
 * @property array $multiple
 * @property int $result null -> not yet answered, -1 -> not answered in time, 0 -> wrong, 1 -> correct
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestionAnswer ofUser($userId)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestionAnswer ofGame($gameId)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestionAnswer whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestionAnswer whereGameQuestionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestionAnswer whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestionAnswer whereQuestionAnswerId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestionAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestionAnswer whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameQuestionAnswer ofRound($roundId)
 * @method static \Illuminate\Database\Eloquent\Builder|GameQuestionAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GameQuestionAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GameQuestionAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder|GameQuestionAnswer whereMultiple($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GameQuestionAnswer whereResult($value)
 */
	class IdeHelperGameQuestionAnswer {}
}

namespace App\Models{
/**
 * App\Models\GameRound.
 *
 * @property-read \App\Models\Game $game
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GameQuestion[] $gameQuestions
 * @property int $id
 * @property int $game_id
 * @property int $category_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameRound whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameRound whereGameId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameRound whereCategoryId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameRound whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameRound whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Query\Builder|\App\Models\GameRound ofGame($gameId)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GameQuestionAnswer[] $gameQuestionAnswers
 * @property-read int|null $game_question_answers_count
 * @property-read int|null $game_questions_count
 * @method static \Illuminate\Database\Eloquent\Builder|GameRound newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GameRound newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GameRound query()
 */
	class IdeHelperGameRound {}
}

namespace App\Models{
/**
 * App\Models\HelpdeskCategory
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $sortIndex
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskCategory whereSortIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskCategory whereUpdatedAt($value)
 */
	class IdeHelperHelpdeskCategory {}
}

namespace App\Models{
/**
 * App\Models\HelpdeskPage
 *
 * @property int $id
 * @property string $title
 * @property string $type
 * @property string $content
 * @property int|null $category
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\HelpdeskPageFeedback[] $feedbacks
 * @property-read int|null $feedbacks_count
 * @property-read mixed $feedback_count
 * @property-read mixed $has_authenticated_user_feedback
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage query()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage whereUpdatedAt($value)
 */
	class IdeHelperHelpdeskPage {}
}

namespace App\Models{
/**
 * App\Models\HelpdeskPageFeedback
 *
 * @property int $id
 * @property int $page_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPageFeedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPageFeedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPageFeedback query()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPageFeedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPageFeedback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPageFeedback wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPageFeedback whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPageFeedback whereUserId($value)
 */
	class IdeHelperHelpdeskPageFeedback {}
}

namespace App\Models{
/**
 * The `progress` and `steps` fields are quite special. Note that the progress isn't saved in the database but in the cache.
 * 
 * `steps` is an int and defines how many steps there are to do in an import (example for users: checking for validity, creating tags, creating users)
 * `progress` is a float and defines the current progress within a step, so a progress of 2.57 would mean that step 3 is 57% done.
 * 
 * Class Import
 *
 * @property int $id
 * @property int $app_id
 * @property int $creator_id
 * @property int $type
 * @property int $steps
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\App $app
 * @property-read \App\Models\User $creator
 * @method static \Illuminate\Database\Eloquent\Builder|Import newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Import newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Import query()
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereSteps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Import whereUpdatedAt($value)
 */
	class IdeHelperImport {}
}

namespace App\Models{
/**
 * App\Models\IndexCard
 *
 * @property int $id
 * @property int $app_id
 * @property string $front
 * @property string $back
 * @property int|null $category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $cover_image
 * @property string|null $cover_image_url
 * @property string|null $json
 * @property string $type
 * @property-read \App\Models\App $app
 * @property-read \App\Models\Category|null $category
 * @property-read mixed $image_url
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard query()
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereBack($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereCoverImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereCoverImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereFront($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereUpdatedAt($value)
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 */
	class IdeHelperIndexCard {}
}

namespace App\Models{
/**
 * App\Models\KeelearningModel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|KeelearningModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KeelearningModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KeelearningModel query()
 */
	class IdeHelperKeelearningModel {}
}

namespace App\Models\Keywords{
/**
 * App\Models\Keywords\Keyword
 *
 * @property int $id
 * @property int $app_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Keywords\KeywordTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|ContentCategory[] $categories
 * @property-read int|null $categories_count
 * @method static \Illuminate\Database\Eloquent\Builder|Keyword newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Keyword newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Keyword query()
 * @method static \Illuminate\Database\Eloquent\Builder|Keyword whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keyword whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keyword whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keyword whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class IdeHelperKeyword {}
}

namespace App\Models\Keywords{
/**
 * App\Models\Keywords\KeywordTranslation
 *
 * @property int $id
 * @property int $keyword_id
 * @property string $language
 * @property string|null $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Keywords\Keyword $keyword
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation whereKeywordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class IdeHelperKeywordTranslation {}
}

namespace App\Models{
/**
 * App\Models\LearnBoxCard
 *
 * @property int $id
 * @property int $user_id
 * @property int $foreign_id
 * @property int $type
 * @property int $box
 * @property array $userdata
 * @property string $box_entered_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard query()
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard whereBox($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard whereBoxEnteredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard whereUserdata($value)
 */
	class IdeHelperLearnBoxCard {}
}

namespace App\Models{
/**
 * App\Models\LearnBoxCardUserDailyCount
 *
 * @property int $id
 * @property int $user_id
 * @property string $date
 * @property int $count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCardUserDailyCount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCardUserDailyCount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCardUserDailyCount query()
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCardUserDailyCount whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCardUserDailyCount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCardUserDailyCount whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCardUserDailyCount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCardUserDailyCount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCardUserDailyCount whereUserId($value)
 */
	class IdeHelperLearnBoxCardUserDailyCount {}
}

namespace App\Models{
/**
 * App\Models\LearningMaterial
 *
 * @property int $id
 * @property int $learning_material_folder_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $notification_sent_at
 * @property string|null $cover_image
 * @property string|null $cover_image_url
 * @property int $send_notification
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LearningMaterialTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comments\Comment[] $comments
 * @property-read int|null $comment_count
 * @property-read \Illuminate\Database\Eloquent\Collection|CourseContent[] $courseContents
 * @property-read int|null $course_contents_count
 * @property-read mixed $app_id
 * @property-read mixed $views
 * @property-read \App\Models\LearningMaterialFolder $learningMaterialFolder
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Viewcount[] $viewcounts
 * @property-read int|null $viewcounts_count
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial query()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial whereCoverImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial whereCoverImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial whereLearningMaterialFolderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial whereNotificationSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial whereSendNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial whereUpdatedAt($value)
 * @property int $visible
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read int|null $comments_count
 * @property-read mixed $app
 * @property-read Boolean $is_reusable_clone
 * @property-read mixed $watermark
 * @method static \Database\Factories\LearningMaterialFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial whereVisible($value)
 */
	class IdeHelperLearningMaterial {}
}

namespace App\Models{
/**
 * App\Models\LearningMaterialFolder
 *
 * @property int $id
 * @property int $app_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $folder_icon
 * @property string|null $folder_icon_url
 * @property int|null $parent_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LearningMaterialFolderTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \App\Models\App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|LearningMaterialFolder[] $childFolders
 * @property-read int|null $child_folders_count
 * @property-read mixed $icon_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LearningMaterial[] $learningMaterials
 * @property-read int|null $learning_materials_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder query()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder whereFolderIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder whereFolderIconUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder whereUpdatedAt($value)
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 * @property-read LearningMaterialFolder|null $parentFolder
 * @method static \Database\Factories\LearningMaterialFolderFactory factory(...$parameters)
 */
	class IdeHelperLearningMaterialFolder {}
}

namespace App\Models{
/**
 * App\Models\LearningMaterialFolderTranslation
 *
 * @property int $id
 * @property int $learning_material_folder_id
 * @property string $language
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\LearningMaterialFolder $learningMaterialFolder
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolderTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolderTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolderTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolderTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolderTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolderTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolderTranslation whereLearningMaterialFolderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolderTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolderTranslation whereUpdatedAt($value)
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 */
	class IdeHelperLearningMaterialFolderTranslation {}
}

namespace App\Models{
/**
 * App\Models\LearningMaterialTranslation
 *
 * @property int $id
 * @property int $learning_material_id
 * @property string $language
 * @property string $title
 * @property string $description
 * @property string $link
 * @property string $file
 * @property string|null $file_url
 * @property string $file_type
 * @property int|null $file_size_kb
 * @property int|null $wbt_subtype
 * @property string|null $wbt_custom_entrypoint
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $wbt_id
 * @property int $download_disabled
 * @property-read \App\Models\LearningMaterial $learningMaterial
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereDownloadDisabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereFileSizeKb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereFileUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereLearningMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereWbtId($value)
 * @property int $show_watermark
 * @property-read \App\Models\AzureVideo|null $azureVideo
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereShowWatermark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereWbtCustomEntrypoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereWbtSubtype($value)
 */
	class IdeHelperLearningMaterialTranslation {}
}

namespace App\Models{
/**
 * App\Models\Like
 *
 * @property int $id
 * @property int $foreign_type
 * @property int $foreign_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Like newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Like newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Like query()
 * @method static \Illuminate\Database\Eloquent\Builder|Like whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Like whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Like whereForeignType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Like whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Like whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Like whereUserId($value)
 */
	class IdeHelperLike {}
}

namespace App\Models{
/**
 * App\Models\MailTemplate
 *
 * @property int $id
 * @property int $app_id
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MailTemplateTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \App\Models\App $app
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplate whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplate whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplate whereUpdatedAt($value)
 */
	class IdeHelperMailTemplate {}
}

namespace App\Models{
/**
 * App\Models\MailTemplateTranslation
 *
 * @property int $id
 * @property int $mail_template_id
 * @property string $language
 * @property string $title
 * @property string $body
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MailTemplate $mailTemplate
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation whereMailTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation whereUpdatedAt($value)
 */
	class IdeHelperMailTemplateTranslation {}
}

namespace App\Models{
/**
 * App\Models\News
 *
 * @property int $id
 * @property int $app_id
 * @property string $news_tags
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $active_until
 * @property Carbon|null $published_at
 * @property Carbon|null $notification_sent_at
 * @property string|null $cover_image
 * @property string|null $cover_image_url
 * @property int $send_notification
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NewsTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \App\Models\App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comments\Comment[] $comments
 * @property-read int|null $comment_count
 * @property-read mixed $views
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Viewcount[] $viewcounts
 * @property-read int|null $viewcounts_count
 * @method static \Illuminate\Database\Eloquent\Builder|News newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|News newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|News query()
 * @method static \Illuminate\Database\Eloquent\Builder|News tagRights()
 * @method static \Illuminate\Database\Eloquent\Builder|News tagRightsJoin($tagIds = null)
 * @method static \Illuminate\Database\Eloquent\Builder|News visibleToUser(\App\Models\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereActiveUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereCoverImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereCoverImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereNewsTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereNotificationSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereSendNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereUpdatedAt($value)
 * @property int|null $image_transformation_id
 * @property-read int|null $comments_count
 * @method static \Database\Factories\NewsFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|News ofApp($appId)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereImageTransformationId($value)
 */
	class IdeHelperNews {}
}

namespace App\Models{
/**
 * App\Models\NewsTranslation
 *
 * @property int $id
 * @property int $news_id
 * @property string $language
 * @property string $title
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\News $news
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation whereNewsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation whereUpdatedAt($value)
 */
	class IdeHelperNewsTranslation {}
}

namespace App\Models{
/**
 * App\Models\NotificationSubscription
 *
 * @property int $id
 * @property int $user_id
 * @property int $foreign_id
 * @property int $foreign_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $relatable
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationSubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationSubscription whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationSubscription whereForeignType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationSubscription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationSubscription whereUserId($value)
 * @mixin \Eloquent
 */
	class IdeHelperNotificationSubscription {}
}

namespace App\Models{
/**
 * App\Models\OpenIdToken
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|OpenIdToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OpenIdToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OpenIdToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|OpenIdToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OpenIdToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OpenIdToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OpenIdToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OpenIdToken whereUserId($value)
 */
	class IdeHelperOpenIdToken {}
}

namespace App\Models{
/**
 * App\Models\Page.
 *
 * @property-read App $app
 * @property int $id
 * @property int $app_id
 * @property string $title
 * @property string $content
 * @property bool $visible
 * @property bool $public
 * @property bool $show_on_auth
 * @property bool $show_in_footer
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int|null $parent_id
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Page whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Page whereAppId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Page whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Page whereContent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Page whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Page whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Page whereVisible($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Page visibleToUser(\App\Models\User $user)
 * @mixin \Eloquent
 * @property int|null $parent_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PageTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @method static \Illuminate\Database\Eloquent\Builder|Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Page query()
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page wherePublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereShowInFooter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereShowOnAuth($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @method static \Database\Factories\PageFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Page tagRights(?\App\Models\User $admin = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Page visibleToAppProfile(\App\Models\AppProfile $appProfile)
 */
	class IdeHelperPage {}
}

namespace App\Models{
/**
 * App\Models\PageTranslation
 *
 * @property int $id
 * @property int $page_id
 * @property string $language
 * @property string $title
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Page $page
 * @method static \Illuminate\Database\Eloquent\Builder|PageTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PageTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PageTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|PageTranslation whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageTranslation wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageTranslation whereUpdatedAt($value)
 */
	class IdeHelperPageTranslation {}
}

namespace App\Models{
/**
 * App\Models\PrivacyNoteConfirmation
 *
 * @property int $id
 * @property int $user_id
 * @property int $accepted_version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyNoteConfirmation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyNoteConfirmation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyNoteConfirmation query()
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyNoteConfirmation whereAcceptedVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyNoteConfirmation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyNoteConfirmation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyNoteConfirmation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrivacyNoteConfirmation whereUserId($value)
 */
	class IdeHelperPrivacyNoteConfirmation {}
}

namespace App\Models{
/**
 * App\Models\Question.
 *
 * @property-read \App\Models\App $app
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionAnswer[] $questionAnswers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionAttachment[] $attachments
 * @property int $id
 * @property int $app_id
 * @property string $title
 * @property bool $visible
 * @property string $category_id
 * @property int $type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question ofCategoryWithId($categoryId)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question visible()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereAppId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereVisible($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereCategoryId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $answertime
 * @property int $confirmed
 * @property int|null $creator_user_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read int|null $attachments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|CourseContentAttachment[] $courseContents
 * @property-read int|null $course_contents_count
 * @property-read \App\Models\User|null $creator_user
 * @property-read mixed $has_only_image_attachments
 * @property-read mixed $realanswertime
 * @property-read int|null $question_answers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionDifficulty[] $questionDifficulties
 * @property-read int|null $question_difficulties_count
 * @method static \Illuminate\Database\Eloquent\Builder|Question newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Question newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Question ofApp($appId)
 * @method static \Illuminate\Database\Eloquent\Builder|Question query()
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereAnswertime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereConfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereCreatorUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question withoutIndexCards()
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 */
	class IdeHelperQuestion {}
}

namespace App\Models{
/**
 * App\Models\QuestionAnswer.
 *
 * @property-read \App\Models\Question $question
 * @property int $id
 * @property int $question_id
 * @property string $content
 * @property bool $correct
 * @property string $feedback
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionAnswer correct()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GameQuestionAnswer[] $gameQuestionAnswer
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionAnswer whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionAnswer whereQuestionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionAnswer whereContent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionAnswer whereCorrect($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\QuestionAnswer whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionAnswerTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read int|null $game_question_answer_count
 * @property-read mixed $app_id
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswer query()
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 */
	class IdeHelperQuestionAnswer {}
}

namespace App\Models{
/**
 * App\Models\QuestionAnswerTranslation
 *
 * @property int $id
 * @property int $question_answer_id
 * @property string $language
 * @property string $content
 * @property string|null $feedback
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\QuestionAnswer $questionAnswer
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation whereFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation whereQuestionAnswerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation whereUpdatedAt($value)
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 */
	class IdeHelperQuestionAnswerTranslation {}
}

namespace App\Models{
/**
 * Class QuestionAttachment.
 *
 * @property-read Question $question
 * @property int $id
 * @property int $question_id
 * @property int $type
 * @property string $url
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $attachment
 * @property string|null $attachment_url
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment query()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment whereAttachment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment whereAttachmentUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment whereUpdatedAt($value)
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 */
	class IdeHelperQuestionAttachment {}
}

namespace App\Models{
/**
 * App\Models\QuestionDifficulty
 *
 * @property int $id
 * @property int $question_id
 * @property int|null $user_id
 * @property string $difficulty
 * @property int $sample_size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Question $question
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty query()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty whereDifficulty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty whereSampleSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionDifficulty whereUserId($value)
 */
	class IdeHelperQuestionDifficulty {}
}

namespace App\Models{
/**
 * App\Models\QuestionTranslation
 *
 * @property int $id
 * @property int $question_id
 * @property string $language
 * @property string $title
 * @property string|null $latex
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Question $question
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation whereLatex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation whereUpdatedAt($value)
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 */
	class IdeHelperQuestionTranslation {}
}

namespace App\Models{
/**
 * App\Models\QuizTeam.
 *
 * @property-read App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuizTeamMember[] $quizTeamMembers
 * @property int $id
 * @property int $app_id
 * @property int $owner_id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $members
 * @method static \Illuminate\Database\Query\Builder|QuizTeam whereId($value)
 * @method static \Illuminate\Database\Query\Builder|QuizTeam whereAppId($value)
 * @method static \Illuminate\Database\Query\Builder|QuizTeam whereName($value)
 * @method static \Illuminate\Database\Query\Builder|QuizTeam whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|QuizTeam whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|QuizTeam whereOwnerId($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|Competition[] $competitions
 * @property-read int|null $competitions_count
 * @property-read int|null $members_count
 * @method static Builder|QuizTeam newModelQuery()
 * @method static Builder|QuizTeam newQuery()
 * @method static Builder|QuizTeam ofApp($appId)
 * @method static Builder|QuizTeam query()
 */
	class IdeHelperQuizTeam {}
}

namespace App\Models{
/**
 * App\Models\QuizTeamMember.
 *
 * @property-read QuizTeam $quizTeam
 * @property-read User $user
 * @property int $id
 * @property int $quiz_team_id
 * @property int $user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static Builder|QuizTeamMember whereId($value)
 * @method static Builder|QuizTeamMember whereQuizTeamId($value)
 * @method static Builder|QuizTeamMember whereUserId($value)
 * @method static Builder|QuizTeamMember whereCreatedAt($value)
 * @method static Builder|QuizTeamMember whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|QuizTeamMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuizTeamMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuizTeamMember query()
 */
	class IdeHelperQuizTeamMember {}
}

namespace App\Models{
/**
 * App\Models\Reminder
 *
 * @property int $id
 * @property string $foreign_id
 * @property int $app_id
 * @property int|null $user_id
 * @property int $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $days_offset
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ReminderMetadata[] $metadata
 * @property-read int|null $metadata_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder query()
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder whereDaysOffset($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder whereUserId($value)
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 */
	class IdeHelperReminder {}
}

namespace App\Models{
/**
 * App\Models\ReminderMetadata
 *
 * @property int $id
 * @property int $reminder_id
 * @property string $key
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Reminder $reminder
 * @method static \Illuminate\Database\Eloquent\Builder|ReminderMetadata newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReminderMetadata newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReminderMetadata query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReminderMetadata whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReminderMetadata whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReminderMetadata whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReminderMetadata whereReminderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReminderMetadata whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReminderMetadata whereValue($value)
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 */
	class IdeHelperReminderMetadata {}
}

namespace App\Models{
/**
 * App\Models\Reporting
 *
 * @property int $id
 * @property int $app_id
 * @property array $tag_ids
 * @property array $group_ids
 * @property array $category_ids
 * @property array $emails
 * @property string $interval
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\App $app
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereCategoryIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereGroupIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereTagIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereUpdatedAt($value)
 * @property int $type
 * @method static \Illuminate\Database\Eloquent\Builder|Reporting whereType($value)
 */
	class IdeHelperReporting {}
}

namespace App\Models{
/**
 * App\Models\SuggestedQuestion.
 *
 * @property-read \App\Models\App $app
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SuggestedQuestionAnswer[] $questionAnswers
 * @property int $id
 * @property int $app_id
 * @property int $category_id
 * @property string $title
 * @property int $user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Category $category
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestion whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestion whereAppId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestion whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestion whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestion whereCategoryId($value)
 * @mixin \Eloquent
 * @property-read int|null $question_answers_count
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestedQuestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestedQuestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestedQuestion query()
 */
	class IdeHelperSuggestedQuestion {}
}

namespace App\Models{
/**
 * App\Models\SuggestedQuestionAnswer.
 *
 * @property-read \App\Models\SuggestedQuestion $suggestedQuestion
 * @property int $id
 * @property int $suggested_question_id
 * @property string $content
 * @property bool $correct
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestionAnswer whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestionAnswer whereSuggestedQuestionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestionAnswer whereContent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestionAnswer whereCorrect($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestionAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestionAnswer whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestedQuestionAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestedQuestionAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestedQuestionAnswer query()
 */
	class IdeHelperSuggestedQuestionAnswer {}
}

namespace App\Models{
/**
 * App\Models\Tag.
 *
 * @property int $id
 * @property string $label
 * @property int $creator_id
 * @property int $tag_group_id
 * @property bool $exclusive
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property-read \App\Models\User $creator
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereLabel($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereCreatorId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereDeletedAt($value)
 * @mixin \Eloquent
 * @property int $app_id
 * @property-read \App\Models\App $app
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereAppId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Category[] $categories
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Competition[] $competitions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\Course[] courses
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereExclusive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag ofApp($appId)
 * @property int $hideHighscore
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Categorygroup[] $categorygroups
 * @property-read int|null $categorygroups_count
 * @property-read int|null $competitions_count
 * @property-read int|null $courses_count
 * @property-read \App\Models\TagGroup|null $tagGroup
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Webinar[] $webinars
 * @property-read int|null $webinars_count
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newQuery()
 * @method static \Illuminate\Database\Query\Builder|Tag onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag rights(\App\Models\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereHideHighscore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereTagGroupId($value)
 * @method static \Illuminate\Database\Query\Builder|Tag withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Tag withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Advertisements\Advertisement[] $advertisements
 * @property-read int|null $advertisements_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AnalyticsEvent[] $analyticsEvents
 * @property-read int|null $analytics_events_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContentCategories\ContentCategory[] $contentcategories
 * @property-read int|null $contentcategories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\Course[] $courses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LearningMaterialFolder[] $learningmaterialfolders
 * @property-read int|null $learningmaterialfolders_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LearningMaterial[] $learningmaterials
 * @property-read int|null $learningmaterials_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\News[] $news
 * @property-read int|null $news_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Page[] $pages
 * @property-read int|null $pages_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Test[] $tests
 * @property-read int|null $tests_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Voucher[] $vouchers
 * @property-read int|null $vouchers_count
 * @method static \Database\Factories\TagFactory factory(...$parameters)
 */
	class IdeHelperTag {}
}

namespace App\Models{
/**
 * App\Models\TagGroup
 *
 * @property int $id
 * @property int $app_id
 * @property string $name
 * @property int $can_have_duplicates
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $signup_selectable
 * @property int $show_highscore_tag
 * @property int $signup_required
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup ofApp($appId)
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup whereCanHaveDuplicates($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup whereShowHighscoreTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup whereSignupRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup whereSignupSelectable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TagGroup whereUpdatedAt($value)
 */
	class IdeHelperTagGroup {}
}

namespace App\Models{
/**
 * Class Test.
 *
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $active_until
 * @property int|null $quiz_team_id
 * @property string $name
 * @property int|null $minutes
 * @property App $app
 * @property int $app_id
 * @property int $min_rate
 * @property int $attempts
 * @property int $repeatable_after_pass
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $archived
 * @property int $no_download
 * @property int $mode
 * @property string|null $cover_image_url
 * @property string $icon_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TestTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $awardTags
 * @property-read int|null $award_tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CertificateTemplate[] $certificateTemplates
 * @property-read int|null $certificate_templates_count
 * @property-read mixed $question_count
 * @property-read mixed $url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TestSubmission[] $submissions
 * @property-read int|null $submissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TestCategory[] $testCategories
 * @property-read int|null $test_categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TestQuestion[] $testQuestions
 * @property-read int|null $test_questions_count
 * @method static Builder|Test newModelQuery()
 * @method static Builder|Test newQuery()
 * @method static Builder|Test ofApp($appId)
 * @method static Builder|Test query()
 * @method static Builder|Test tagRights()
 * @method static Builder|Test tagRightsJoin($tagIds = null)
 * @method static Builder|Test whereActiveUntil($value)
 * @method static Builder|Test whereAppId($value)
 * @method static Builder|Test whereArchived($value)
 * @method static Builder|Test whereAttempts($value)
 * @method static Builder|Test whereCoverImageUrl($value)
 * @method static Builder|Test whereCreatedAt($value)
 * @method static Builder|Test whereQuizTeamId($value)
 * @method static Builder|Test whereIconUrl($value)
 * @method static Builder|Test whereId($value)
 * @method static Builder|Test whereMinRate($value)
 * @method static Builder|Test whereMinutes($value)
 * @method static Builder|Test whereMode($value)
 * @method static Builder|Test whereNoDownload($value)
 * @method static Builder|Test whereRepeatableAfterPass($value)
 * @method static Builder|Test whereUpdatedAt($value)
 * @property int $send_certificate_to_admin
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereSendCertificateToAdmin($value)
 */
	class IdeHelperTest {}
}

namespace App\Models{
/**
 * App\Models\TestCategory
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $test_id
 * @property int $category_id
 * @property int $question_amount
 * @property-read \App\Models\Category $category
 * @property-read \App\Models\Test $test
 * @method static \Illuminate\Database\Eloquent\Builder|TestCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|TestCategory whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestCategory whereQuestionAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestCategory whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestCategory whereUpdatedAt($value)
 */
	class IdeHelperTestCategory {}
}

namespace App\Models{
/**
 * App\Models\TestQuestion
 *
 * @property int $id
 * @property int $test_id
 * @property int $position
 * @property int $question_id
 * @property int|null $points
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TestSubmissionAnswer[] $answers
 * @property-read int|null $answers_count
 * @property-read mixed $realpoints
 * @property-read \App\Models\Question $question
 * @property-read \App\Models\Test $test
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion query()
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestQuestion whereUpdatedAt($value)
 */
	class IdeHelperTestQuestion {}
}

namespace App\Models{
/**
 * App\Models\TestSubmission
 *
 * @property int $id
 * @property int $test_id
 * @property int $user_id
 * @property int|null $result
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Test $test
 * @property-read Collection|\App\Models\TestSubmissionAnswer[] $testSubmissionAnswers
 * @property-read int|null $test_submission_answers_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmission query()
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmission whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmission whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmission whereUserId($value)
 */
	class IdeHelperTestSubmission {}
}

namespace App\Models{
/**
 * App\Models\TestSubmissionAnswer
 *
 * @property int $id
 * @property int|null $test_question_id
 * @property int $question_id
 * @property int $test_submission_id
 * @property string|null $answer_ids
 * @property int|null $result
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Question $question
 * @property-read \App\Models\TestQuestion|null $testQuestion
 * @property-read \App\Models\TestSubmission $testSubmission
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer whereAnswerIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer whereTestQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer whereTestSubmissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestSubmissionAnswer whereUpdatedAt($value)
 */
	class IdeHelperTestSubmissionAnswer {}
}

namespace App\Models{
/**
 * App\Models\TestTranslation
 *
 * @property int $id
 * @property int $test_id
 * @property string $language
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Test $test
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation whereUpdatedAt($value)
 */
	class IdeHelperTestTranslation {}
}

namespace App\Models{
/**
 * App\Models\Todolist
 *
 * @property int $id
 * @property int $app_id
 * @property int $foreign_id
 * @property int $foreign_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TodolistItem[] $todolistItems
 * @property-read int|null $todolist_items_count
 * @method static \Illuminate\Database\Eloquent\Builder|Todolist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Todolist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Todolist query()
 * @method static \Illuminate\Database\Eloquent\Builder|Todolist whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Todolist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Todolist whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Todolist whereForeignType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Todolist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Todolist whereUpdatedAt($value)
 */
	class IdeHelperTodolist {}
}

namespace App\Models{
/**
 * App\Models\TodolistItem
 *
 * @property int $id
 * @property int $todolist_id
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TodolistItemTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TodolistItemAnswer[] $answers
 * @property-read int|null $answers_count
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 * @property-read \App\Models\Todolist|null $todolist
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItem wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItem whereTodolistId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItem whereUpdatedAt($value)
 */
	class IdeHelperTodolistItem {}
}

namespace App\Models{
/**
 * App\Models\TodolistItemAnswer
 *
 * @property int $id
 * @property int $todolist_item_id
 * @property int $user_id
 * @property int $is_done
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TodolistItem|null $todolistItem
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemAnswer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemAnswer whereIsDone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemAnswer whereTodolistItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemAnswer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemAnswer whereUserId($value)
 */
	class IdeHelperTodolistItemAnswer {}
}

namespace App\Models{
/**
 * App\Models\TodolistItemTranslation
 *
 * @property int $id
 * @property int $todolist_item_id
 * @property string $language
 * @property string $title
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 * @property-read \App\Models\TodolistItem|null $todolistItem
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemTranslation whereTodolistItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TodolistItemTranslation whereUpdatedAt($value)
 */
	class IdeHelperTodolistItemTranslation {}
}

namespace App\Models{
/**
 * App\Models\Page.
 *
 * @property-read \App\Models\App $app
 * @property int $id
 * @property int $user_id
 * @property string $question_id
 * @property string $answer_ids
 * @property bool $correct
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @mixin \Eloquent
 * @property-read \App\Models\Question $question
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer whereAnswerIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer whereCorrect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingAnswer whereUserId($value)
 */
	class IdeHelperTrainingAnswer {}
}

namespace App\Models{
/**
 * App\Models\TranslationStatus
 *
 * @property int $id
 * @property int $app_id
 * @property string $foreign_type
 * @property int $foreign_id
 * @property \Illuminate\Support\Carbon|null $autotranslation_running_since
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $language
 * @property array $field_statuses
 * @property bool $is_outdated
 * @property int|null $last_updated_by_id
 * @property int $is_autotranslated
 * @property-read \App\Models\App|null $app
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationStatus whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationStatus whereAutotranslationRunningSince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationStatus whereFieldStatuses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationStatus whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationStatus whereForeignType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationStatus whereIsAutotranslated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationStatus whereIsOutdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationStatus whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationStatus whereLastUpdatedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslationStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class IdeHelperTranslationStatus {}
}

namespace App\Models{
/**
 * App\Models\User.
 *
 * @property-read \App\Models\App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Game[] $gameAsPlayer1
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Game[] $gameAsPlayer2
 * @property int $id
 * @property int $app_id
 * @property string $username
 * @property string $email
 * @property bool $active
 * @property string $password
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $tos_accepted
 * @property string $remember_token
 * @property bool $is_admin
 * @property bool $is_bot
 * @property string $language
 * @property string $fcm_id
 * @property string $apns_id
 * @property string $gcm_id_browser
 * @property string $gcm_browser_p256dh
 * @property string $gcm_browser_auth
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User butNotThisOne()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User ofSameApp()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuizTeam[] $quizTeams
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereAppId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereUsername($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereTosAccepted($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereRememberToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereActive($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User active()
 * @property string $tag_ids
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereGcmId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereTagIds($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $createdTags
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property string|null $deleted_at
 * @property string|null $country
 * @property int $failed_login_attempts
 * @property int $is_dummy
 * @property string $firstname
 * @property string $lastname
 * @property string|null $avatar
 * @property string|null $avatar_url
 * @property \datetime|null $expires_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AccessLog[] $accessLogs
 * @property-read int|null $access_logs_count
 * @property-read int|null $created_tags_count
 * @property-read int|null $game_as_player1_count
 * @property-read int|null $game_as_player2_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GamePoint[] $gamePoints
 * @property-read int|null $game_points_count
 * @property-read mixed $permissions_list
 * @property-read int|null $quiz_team_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LearnBoxCard[] $learnBoxCards
 * @property-read int|null $learn_box_cards_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserPermission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionDifficulty[] $questionDifficulties
 * @property-read int|null $question_difficulties_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Reminder[] $reminders
 * @property-read int|null $reminders_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SuggestedQuestion[] $suggestedQuestions
 * @property-read int|null $suggested_questions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tagRights
 * @property-read int|null $tag_rights_count
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TestSubmission[] $testSubmissions
 * @property-read int|null $test_submissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TrainingAnswer[] $trainingAnswers
 * @property-read int|null $training_answers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\VoucherCode[] $voucherCodes
 * @property-read int|null $voucher_codes_count
 * @method static Builder|User activeOfApp($appId)
 * @method static Builder|User admin()
 * @method static Builder|User bot()
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User ofApp($appId)
 * @method static Builder|User powerlessAdmin()
 * @method static Builder|User query()
 * @method static Builder|User tagRights($tagIds = null)
 * @method static Builder|User tagRightsJoin($tagIds = null)
 * @method static Builder|User whereApnsId($value)
 * @method static Builder|User whereAvatar($value)
 * @method static Builder|User whereAvatarUrl($value)
 * @method static Builder|User whereCountry($value)
 * @method static Builder|User whereDeletedAt($value)
 * @method static Builder|User whereExpiresAt($value)
 * @method static Builder|User whereFailedLoginAttempts($value)
 * @method static Builder|User whereFcmId($value)
 * @method static Builder|User whereFirstname($value)
 * @method static Builder|User whereGcmBrowserAuth($value)
 * @method static Builder|User whereGcmBrowserP256dh($value)
 * @method static Builder|User whereGcmIdBrowser($value)
 * @method static Builder|User whereIsAdmin($value)
 * @method static Builder|User whereIsBot($value)
 * @method static Builder|User whereIsDummy($value)
 * @method static Builder|User whereLanguage($value)
 * @method static Builder|User whereLastname($value)
 * @property int $is_api_user
 * @property int $force_password_reset
 * @property int $is_keeunit
 * @property int|null $user_role_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AuthToken[] $authTokens
 * @property-read int|null $auth_tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comments\Comment[] $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\CourseParticipation[] $courseParticipations
 * @property-read int|null $course_participations_count
 * @property-read string $displayname
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Courses\Course[] $individualCourses
 * @property-read int|null $individual_courses_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserMetafield[] $metafields
 * @property-read int|null $metafields_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OpenIdToken[] $openIdTokens
 * @property-read int|null $open_id_tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PrivacyNoteConfirmation[] $privacyNoteConfirmations
 * @property-read int|null $privacy_note_confirmations_count
 * @property-read int|null $quiz_teams_count
 * @property-read \App\Models\UserRole|null $role
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tagRightsRelation
 * @property-read int|null $tag_rights_relation_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User showInLists()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereForcePasswordReset($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsApiUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsKeeunit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUserRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutMainAdmins()
 */
	class IdeHelperUser {}
}

namespace App\Models{
/**
 * App\Models\UserMetafield
 *
 * @property int $id
 * @property int $user_id
 * @property string $key
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserMetafield newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserMetafield newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserMetafield query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserMetafield whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserMetafield whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserMetafield whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserMetafield whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserMetafield whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserMetafield whereValue($value)
 * @mixin \Eloquent
 */
	class IdeHelperUserMetafield {}
}

namespace App\Models{
/**
 * App\Models\UserNotificationSetting
 *
 * @property int $id
 * @property int $user_id
 * @property string $mail
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting whereMail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting whereUserId($value)
 * @property string $notification
 * @property int $push_disabled
 * @property int $mail_disabled
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting whereMailDisabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting whereNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting wherePushDisabled($value)
 */
	class IdeHelperUserNotificationSetting {}
}

namespace App\Models{
/**
 * App\Models\UserPermission
 *
 * @property int $id
 * @property int $user_id
 * @property string $permission
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission wherePermission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission whereUserId($value)
 */
	class IdeHelperUserPermission {}
}

namespace App\Models{
/**
 * App\Models\UserQuestionData
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id
 * @property int $question_id
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Question|null $question
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserQuestionData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserQuestionData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserQuestionData ofQuestion(\App\Models\Question $question)
 * @method static \Illuminate\Database\Eloquent\Builder|UserQuestionData ofUser(\App\Models\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|UserQuestionData query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserQuestionData whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserQuestionData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserQuestionData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserQuestionData whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserQuestionData whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserQuestionData whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserQuestionData whereUserId($value)
 * @mixin \Eloquent
 */
	class IdeHelperUserQuestionData {}
}

namespace App\Models{
/**
 * App\Models\UserRole
 *
 * @property int $id
 * @property int $app_id
 * @property string $name
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $is_main_admin
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserRoleRight[] $rights
 * @property-read int|null $rights_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole ofApp($appId)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole whereIsMainAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole whereUpdatedAt($value)
 */
	class IdeHelperUserRole {}
}

namespace App\Models{
/**
 * App\Models\UserRoleRight
 *
 * @property int $id
 * @property int $user_role_id
 * @property string $right
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CloneRecord|null $cloneRecord
 * @property-read Boolean $is_reusable_clone
 * @property-read \App\Models\UserRole|null $userRole
 * @method static \Illuminate\Database\Eloquent\Builder|UserRoleRight newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRoleRight newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRoleRight query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRoleRight whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRoleRight whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRoleRight whereRight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRoleRight whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRoleRight whereUserRoleId($value)
 */
	class IdeHelperUserRoleRight {}
}

namespace App\Models{
/**
 * App\Models\Viewcount
 *
 * @property int $id
 * @property int $foreign_id
 * @property string $foreign_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_id
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $foreign
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount ofApp($appId)
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount query()
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount whereForeignType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount whereUserId($value)
 * @property-read \App\Models\User|null $user
 */
	class IdeHelperViewcount {}
}

namespace App\Models{
/**
 * App\Models\Voucher
 *
 * @property int $id
 * @property string $name
 * @property int $amount
 * @property int $app_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $type
 * @property int $validity_interval
 * @property int|null $validity_duration
 * @property-read mixed $current_amount
 * @property-read mixed $selected_tags
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\VoucherCode[] $voucherCodes
 * @property-read int|null $voucher_codes_count
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher query()
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereValidityDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereValidityInterval($value)
 * @property int $archived
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereArchived($value)
 */
	class IdeHelperVoucher {}
}

namespace App\Models{
/**
 * App\Models\VoucherCode
 *
 * @property int $id
 * @property int $voucher_id
 * @property string $code
 * @property int|null $user_id
 * @property Carbon|null $cash_in_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\Voucher $voucher
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode whereCashInDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode whereVoucherId($value)
 */
	class IdeHelperVoucherCode {}
}

namespace App\Models{
/**
 * App\Models\Webinar.
 *
 * @property int $id
 * @property int $app_id
 * @property string $topic
 * @property string $description
 * @property \Carbon\Carbon $starts_at
 * @property int $duration_minutes set to null for open ended
 * @property bool $send_reminder
 * @property \Carbon\Carbon $reminder_sent_at
 * @property bool $show_recordings
 * @property int $samba_id
 * @property-read \App\Models\App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WebinarAdditionalUser[] $additionalUsers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WebinarParticipant[] $participants
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WebinarAdditionalUser[] $additionalExternalUsers
 * @property-read int|null $additional_external_users_count
 * @property-read int|null $additional_users_count
 * @property-read int|null $participants_count
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar query()
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereDurationMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereReminderSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereSambaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereSendReminder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereShowRecordings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereTopic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereUpdatedAt($value)
 */
	class IdeHelperWebinar {}
}

namespace App\Models{
/**
 * App\Models\WebinarAdditionalUser
 *
 * @property int $id
 * @property int $webinar_id
 * @property int $user_id
 * @property string $email
 * @property string $name
 * @property int $role
 * @property-read User $user
 * @property-read Webinar $webinar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\WebinarParticipant|null $participant
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser whereWebinarId($value)
 */
	class IdeHelperWebinarAdditionalUser {}
}

namespace App\Models{
/**
 * App\Models\WebinarParticipant
 *
 * @property int $webinar_id
 * @property int $user_id
 * @property int $webinar_additional_user_id
 * @property string $join_link
 * @property-read User $user
 * @property-read Webinar $webinar
 * @property-read WebinarAdditionalUser $additionalUser
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $samba_invitee_id
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant query()
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant whereJoinLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant whereSambaInviteeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant whereWebinarAdditionalUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant whereWebinarId($value)
 */
	class IdeHelperWebinarParticipant {}
}


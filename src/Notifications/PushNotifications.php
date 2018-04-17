<?php
namespace Levaral\Core\Notifications;

namespace Illuminate\Notifications\Messages;

class PushNotifications extends SimpleMessage
{
    /**
     * An Expo push token specifying the recipient of this message.
     *
     * @var array|string
     */
    public $to;

    /**
     * A JSON object delivered to your app. It may be up to about 4KiB; the total
     * notification payload sent to Apple and Google must be at most 4KiB or else
     * you will get a "Message Too Big" error.
     *
     * @var mixed
     */
    public $data;

    /**
     * The title to display in the notification. Devices often display this in
     * bold above the notification body. Only the title might be displayed on
     * devices with smaller screens like Apple Watch.
     *
     * @var string
     */
    public $title;

    /**
     * The message to display in the notification
     *
     * @var string
     */
    public $body;

    /**
     * A sound to play when the recipient receives this notification. Specify
     * "default" to play the device's default notification sound, or omit this
     * field to play no sound.
     *
     * @var string|null
     */
    public $sound;

    /**
     * Time to Live: the number of seconds for which the message may be kept
     * around for redelivery if it hasn't been delivered yet. Defaults to 0.
     *
     * On Android, we make a best effort to deliver messages with zero TTL
     * immediately and do not throttle them
     *
     * This field takes precedence over `expiration` when both are specified.
     *
     * @var integer
     */
    public $ttl;

    /**
     * A timestamp since the UNIX epoch specifying when the message expires. This
     * has the same effect as the `ttl` field and is just an absolute timestamp
     * instead of a relative time.
     *
     * @var integer
     */
    public $expiration;

    /**
     * The delivery priority of the message. Specify "default" or omit this field
     * to use the default priority on each platform, which is "normal" on Android
     * and "high" on iOS.
     *
     * On Android, normal-priority messages won't open network connections on
     * sleeping devices and their delivery may be delayed to conserve the battery.
     * High-priority messages are delivered immediately if possible and may wake
     * sleeping devices to open network connections, consuming energy.
     *
     * On iOS, normal-priority messages are sent at a time that takes into account
     * power considerations for the device, and may be grouped and delivered in
     * bursts. They are throttled and may not be delivered by Apple. High-priority
     * messages are sent immediately. Normal priority corresponds to APNs priority
     * level 5 and high priority to 10.
     *
     * @var string
     */
    public $priority;

    // iOS-specific fields

    /**
     * Number to display in the badge on the app icon. Specify zero to clear the
     * badge.
     *
     * @var integer
     */
    public $badge;
}
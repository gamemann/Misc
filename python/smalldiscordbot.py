import datetime

import discord
from discord.ext import tasks, commands

class BayleeBot(discord.Client):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)

        # Set default values.
        self.mustnotify = False
        self.enable = False

        # Run tasks.
        self.checktime.start()

    async def on_ready(self):
        print("Baylee bot turned on...")

        self.enable = True

    @tasks.loop(minutes=60)
    async def checktime(self):
        # Check if we're connected.
        if self.enable == False:
            return

        # Get time.
        datime = datetime.datetime.now()
        datime = datime.replace(tzinfo=datetime.timezone.utc)

        # Check if we need to notify (hour 14 = 8 AM in Texas).
        if datime.hour == 14 and self.mustnotify == False:
            # Retrieve channel to see message in (#reminders).
            try:
                 chn = await self.fetch_channel(0)
            except discord.NotFound:
                print("Channel not found.")

            # Send the message tagging Baylee!
            await chn.send("<@xxxxxxx> ...")

            # Set notify to true.
            self.mustnotify = True

        elif datime.hour != 14 and self.mustnotify == True:
            # We can notify in the future.
            self.mustnotify = False

bot = BayleeBot()
bot.run('xxxxxxxxxxxxx')
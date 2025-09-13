package game.shan.utils;

import java.util.concurrent.ScheduledFuture;
import java.util.concurrent.TimeUnit;

import game.shan.ShanExtension;

public class TaskHelper {

	public static void startScheduleTask(ShanExtension ext, ScheduledFuture<?> task, Runnable methodToRun, int delay) {
        cancelTask(task);
        
        task = ext.getTaskScheduler().schedule(methodToRun, delay, TimeUnit.SECONDS);
    }
	
	public static void startScheduleTask(ShanExtension ext, Runnable methodToRun, int delay) {
        ext.getTaskScheduler().schedule(methodToRun, delay, TimeUnit.SECONDS);
    }
	
	public static void cancelTask(ScheduledFuture<?> taskToCancel) {
		if (taskToCancel != null) {
            // Check if the task is not already done or canceled before attempting to cancel it
            if (!taskToCancel.isDone() && !taskToCancel.isCancelled()) {
            	taskToCancel.cancel(true);
            }
            // Set the reference to null to indicate there's no running timer
            taskToCancel = null;
        }
    }
	
}

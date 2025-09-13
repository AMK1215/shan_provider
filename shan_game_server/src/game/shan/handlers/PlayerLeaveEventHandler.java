package game.shan.handlers;

import com.smartfoxserver.v2.core.ISFSEvent;
import com.smartfoxserver.v2.core.SFSEventParam;
import com.smartfoxserver.v2.core.SFSEventType;
import com.smartfoxserver.v2.entities.User;
import com.smartfoxserver.v2.exceptions.SFSException;
import com.smartfoxserver.v2.extensions.BaseServerEventHandler;

import game.shan.game.SKMGame;
import game.shan.utils.RoomHelper;

public class PlayerLeaveEventHandler extends BaseServerEventHandler {

	@Override
	public void handleServerEvent(ISFSEvent event) throws SFSException {
		// TODO Auto-generated method stub
		
		
		if (event.getType() == SFSEventType.USER_DISCONNECT || event.getType() == SFSEventType.USER_LEAVE_ROOM)
		{
			User user = (User) event.getParameter(SFSEventParam.USER);
			
			SKMGame game = RoomHelper.getGame(this);
			game.processPlayerLeave(user);
			
		}
	}

}

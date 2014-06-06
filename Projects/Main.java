import org.junit.runner.JUnitCore;
import org.junit.runner.Result;
import org.junit.runner.notification.Failure;


import java.util.ArrayList;

import java.util.regex.*;

public class Main{
	
	public static void main(String[] args) throws ClassNotFoundException {
		
		ArrayList<String> testsAvailable = new ArrayList<String>();
		GestionBDD gestionBDD = new GestionBDD();
		
		int i = 0;
		int projectId = 300;
		String username = "Michel";
		for(String arg : args)
		{
			if(i == 0)
				projectId = Integer.parseInt(arg);
			else if(i == 1)
				username = arg;
			else
				testsAvailable.add(arg);
			i++;
		}
		
		//System.out.println(username+"/"+projectId);
		
		for(String test : testsAvailable)
		{
			ArrayList<String> subtestsAvailable = new ArrayList<String>();
			subtestsAvailable = gestionBDD.getTests(test);
			
			Class<?> cls = Class.forName(test);
			Result result = JUnitCore.runClasses(cls);
			//System.out.println(result.getRunCount() + " test(s) run, " + result.getFailureCount() + " failure(s)"); 
			Pattern p = Pattern.compile("(.*?)\\((.*?)\\)\\:(.*?)$");
			
			for(Failure failure : result.getFailures()) {
			
				System.out.println(failure.toString());
				
				Matcher m = p.matcher(failure.toString());
				if(m.matches())
				{
					String subtestName = m.group(1);
					String errors = m.group(3);
					
					gestionBDD.addResult(projectId, test, subtestName, username, 0, errors);
					
					if(subtestsAvailable.contains(subtestName))
						subtestsAvailable.remove(subtestName);
				}
			}
			for (String subtestSuccess : subtestsAvailable)
			{
				gestionBDD.addResult(projectId, test, subtestSuccess, username, 1, "success");
			}
		}
	}
} 
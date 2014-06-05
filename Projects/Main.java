import org.junit.runner.JUnitCore;
import org.junit.runner.Result;
import org.junit.runner.notification.Failure;


import java.util.ArrayList;

import java.util.regex.*;

public class Main {
	
	public static void main(String[] args) {
	
		
		ArrayList<String> subtestsAvailable = new ArrayList<String>();
		
		int projectId = Integer.parseInt(args[0]);
		String username = args[1];
		String testName = "MoneyTest";
		
		GestionBDD gestionBDD = new GestionBDD();
		
		subtestsAvailable = gestionBDD.getTests(testName);
		
		Result result = JUnitCore.runClasses(MoneyTest.class);
	    System.out.println(result.getRunCount() + " test(s) run, " + result.getFailureCount() + " failure(s)"); 
	    Pattern p = Pattern.compile("(.*?)\\((.*?)\\)\\:(.*?)$");
		
	    for (Failure failure : result.getFailures()) {
			
			Matcher m = p.matcher(failure.toString());
			if(m.matches())
			{
			    String subtestName = m.group(1);
			    String errors = m.group(3);
			    
			    gestionBDD.addResult(projectId, testName, subtestName, username, 0, errors);
			    
			    if(subtestsAvailable.contains(subtestName))
			    	subtestsAvailable.remove(subtestName);
			}
		}
	    for (String subtestSuccess : subtestsAvailable)
		{
			gestionBDD.addResult(projectId, testName, subtestSuccess, username, 1, "success");
		}
	    
	}
} 
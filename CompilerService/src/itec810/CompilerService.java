package itec810;

import java.io.ByteArrayOutputStream;
import java.io.FileDescriptor;
import java.io.FileOutputStream;
import java.io.PrintStream;
import java.lang.reflect.Method;

import javax.tools.Diagnostic;
import javax.tools.DiagnosticCollector;
import javax.tools.JavaFileObject;

import org.mdkt.compiler.InMemoryJavaCompiler;

public class CompilerService {
	
	public String compile(String sourceCode) throws Exception{
		
		DiagnosticCollector<JavaFileObject> diagnostics = new DiagnosticCollector<JavaFileObject>();
		
		Class<?> cls = InMemoryJavaCompiler.compile("ExampleProgram", sourceCode, diagnostics);
		
		if(cls == null){
			String res = "";
			for (Diagnostic diagnostic : diagnostics.getDiagnostics()){
	        	res += diagnostic.toString();
	        }
			throw new Exception(res);
		}
		
        
        Method m = cls.getMethod("main", String[].class);
        String[] params = null;
        
        //Capture start
        ByteArrayOutputStream baos = new ByteArrayOutputStream();
        System.setOut(new PrintStream(baos));
        
        m.invoke(null, (Object)params);
        
        //Capture end
        System.out.flush();
        System.setOut(new PrintStream(new FileOutputStream(FileDescriptor.out)));
        return baos.toString().trim();
	}

}

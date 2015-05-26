package org.mdkt.compiler;

import javax.tools.DiagnosticCollector;
import javax.tools.JavaCompiler;
import javax.tools.JavaFileObject;
import javax.tools.ToolProvider;

import java.io.Writer;
import java.util.Arrays;

/**
 * Created by trung on 5/3/15.
 */
public class InMemoryJavaCompiler {
    static JavaCompiler javac = ToolProvider.getSystemJavaCompiler();

    public static Class<?> compile(String className, String sourceCodeInText, DiagnosticCollector<JavaFileObject> diagnostics) throws Exception {
        SourceCode sourceCode = new SourceCode(className, sourceCodeInText);
        CompiledCode compiledCode = new CompiledCode(className);
        Iterable<? extends JavaFileObject> compilationUnits = Arrays.asList(sourceCode);
        DynamicClassLoader cl = new DynamicClassLoader(ClassLoader.getSystemClassLoader());
        ExtendedStandardJavaFileManager fileManager = new ExtendedStandardJavaFileManager(javac.getStandardFileManager(null, null, null), compiledCode, cl);
        JavaCompiler.CompilationTask task = javac.getTask(null, fileManager, diagnostics, null, null, compilationUnits);
        boolean result = task.call();
        
        return result? cl.loadClass(className): null;
    }
    
    public static Class<?> compile(String className, String sourceCodeInText) throws Exception {
    	return compile(className, sourceCodeInText, null);
    }
}
